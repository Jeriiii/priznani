(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/**
 * Copyright (c) 2014-2015, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the same directory.
 */

module.exports.Dispatcher = require('./lib/Dispatcher')

},{"./lib/Dispatcher":2}],2:[function(require,module,exports){
/*
 * Copyright (c) 2014, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the same directory.
 *
 * @providesModule Dispatcher
 * @typechecks
 */

"use strict";

var invariant = require('./invariant');

var _lastID = 1;
var _prefix = 'ID_';

/**
 * Dispatcher is used to broadcast payloads to registered callbacks. This is
 * different from generic pub-sub systems in two ways:
 *
 *   1) Callbacks are not subscribed to particular events. Every payload is
 *      dispatched to every registered callback.
 *   2) Callbacks can be deferred in whole or part until other callbacks have
 *      been executed.
 *
 * For example, consider this hypothetical flight destination form, which
 * selects a default city when a country is selected:
 *
 *   var flightDispatcher = new Dispatcher();
 *
 *   // Keeps track of which country is selected
 *   var CountryStore = {country: null};
 *
 *   // Keeps track of which city is selected
 *   var CityStore = {city: null};
 *
 *   // Keeps track of the base flight price of the selected city
 *   var FlightPriceStore = {price: null}
 *
 * When a user changes the selected city, we dispatch the payload:
 *
 *   flightDispatcher.dispatch({
 *     actionType: 'city-update',
 *     selectedCity: 'paris'
 *   });
 *
 * This payload is digested by `CityStore`:
 *
 *   flightDispatcher.register(function(payload) {
 *     if (payload.actionType === 'city-update') {
 *       CityStore.city = payload.selectedCity;
 *     }
 *   });
 *
 * When the user selects a country, we dispatch the payload:
 *
 *   flightDispatcher.dispatch({
 *     actionType: 'country-update',
 *     selectedCountry: 'australia'
 *   });
 *
 * This payload is digested by both stores:
 *
 *    CountryStore.dispatchToken = flightDispatcher.register(function(payload) {
 *     if (payload.actionType === 'country-update') {
 *       CountryStore.country = payload.selectedCountry;
 *     }
 *   });
 *
 * When the callback to update `CountryStore` is registered, we save a reference
 * to the returned token. Using this token with `waitFor()`, we can guarantee
 * that `CountryStore` is updated before the callback that updates `CityStore`
 * needs to query its data.
 *
 *   CityStore.dispatchToken = flightDispatcher.register(function(payload) {
 *     if (payload.actionType === 'country-update') {
 *       // `CountryStore.country` may not be updated.
 *       flightDispatcher.waitFor([CountryStore.dispatchToken]);
 *       // `CountryStore.country` is now guaranteed to be updated.
 *
 *       // Select the default city for the new country
 *       CityStore.city = getDefaultCityForCountry(CountryStore.country);
 *     }
 *   });
 *
 * The usage of `waitFor()` can be chained, for example:
 *
 *   FlightPriceStore.dispatchToken =
 *     flightDispatcher.register(function(payload) {
 *       switch (payload.actionType) {
 *         case 'country-update':
 *           flightDispatcher.waitFor([CityStore.dispatchToken]);
 *           FlightPriceStore.price =
 *             getFlightPriceStore(CountryStore.country, CityStore.city);
 *           break;
 *
 *         case 'city-update':
 *           FlightPriceStore.price =
 *             FlightPriceStore(CountryStore.country, CityStore.city);
 *           break;
 *     }
 *   });
 *
 * The `country-update` payload will be guaranteed to invoke the stores'
 * registered callbacks in order: `CountryStore`, `CityStore`, then
 * `FlightPriceStore`.
 */

  function Dispatcher() {
    this.$Dispatcher_callbacks = {};
    this.$Dispatcher_isPending = {};
    this.$Dispatcher_isHandled = {};
    this.$Dispatcher_isDispatching = false;
    this.$Dispatcher_pendingPayload = null;
  }

  /**
   * Registers a callback to be invoked with every dispatched payload. Returns
   * a token that can be used with `waitFor()`.
   *
   * @param {function} callback
   * @return {string}
   */
  Dispatcher.prototype.register=function(callback) {
    var id = _prefix + _lastID++;
    this.$Dispatcher_callbacks[id] = callback;
    return id;
  };

  /**
   * Removes a callback based on its token.
   *
   * @param {string} id
   */
  Dispatcher.prototype.unregister=function(id) {
    invariant(
      this.$Dispatcher_callbacks[id],
      'Dispatcher.unregister(...): `%s` does not map to a registered callback.',
      id
    );
    delete this.$Dispatcher_callbacks[id];
  };

  /**
   * Waits for the callbacks specified to be invoked before continuing execution
   * of the current callback. This method should only be used by a callback in
   * response to a dispatched payload.
   *
   * @param {array<string>} ids
   */
  Dispatcher.prototype.waitFor=function(ids) {
    invariant(
      this.$Dispatcher_isDispatching,
      'Dispatcher.waitFor(...): Must be invoked while dispatching.'
    );
    for (var ii = 0; ii < ids.length; ii++) {
      var id = ids[ii];
      if (this.$Dispatcher_isPending[id]) {
        invariant(
          this.$Dispatcher_isHandled[id],
          'Dispatcher.waitFor(...): Circular dependency detected while ' +
          'waiting for `%s`.',
          id
        );
        continue;
      }
      invariant(
        this.$Dispatcher_callbacks[id],
        'Dispatcher.waitFor(...): `%s` does not map to a registered callback.',
        id
      );
      this.$Dispatcher_invokeCallback(id);
    }
  };

  /**
   * Dispatches a payload to all registered callbacks.
   *
   * @param {object} payload
   */
  Dispatcher.prototype.dispatch=function(payload) {
    invariant(
      !this.$Dispatcher_isDispatching,
      'Dispatch.dispatch(...): Cannot dispatch in the middle of a dispatch.'
    );
    this.$Dispatcher_startDispatching(payload);
    try {
      for (var id in this.$Dispatcher_callbacks) {
        if (this.$Dispatcher_isPending[id]) {
          continue;
        }
        this.$Dispatcher_invokeCallback(id);
      }
    } finally {
      this.$Dispatcher_stopDispatching();
    }
  };

  /**
   * Is this Dispatcher currently dispatching.
   *
   * @return {boolean}
   */
  Dispatcher.prototype.isDispatching=function() {
    return this.$Dispatcher_isDispatching;
  };

  /**
   * Call the callback stored with the given id. Also do some internal
   * bookkeeping.
   *
   * @param {string} id
   * @internal
   */
  Dispatcher.prototype.$Dispatcher_invokeCallback=function(id) {
    this.$Dispatcher_isPending[id] = true;
    this.$Dispatcher_callbacks[id](this.$Dispatcher_pendingPayload);
    this.$Dispatcher_isHandled[id] = true;
  };

  /**
   * Set up bookkeeping needed when dispatching.
   *
   * @param {object} payload
   * @internal
   */
  Dispatcher.prototype.$Dispatcher_startDispatching=function(payload) {
    for (var id in this.$Dispatcher_callbacks) {
      this.$Dispatcher_isPending[id] = false;
      this.$Dispatcher_isHandled[id] = false;
    }
    this.$Dispatcher_pendingPayload = payload;
    this.$Dispatcher_isDispatching = true;
  };

  /**
   * Clear bookkeeping used for dispatching.
   *
   * @internal
   */
  Dispatcher.prototype.$Dispatcher_stopDispatching=function() {
    this.$Dispatcher_pendingPayload = null;
    this.$Dispatcher_isDispatching = false;
  };


module.exports = Dispatcher;

},{"./invariant":3}],3:[function(require,module,exports){
/**
 * Copyright (c) 2014, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the same directory.
 *
 * @providesModule invariant
 */

"use strict";

/**
 * Use invariant() to assert state which your program assumes to be true.
 *
 * Provide sprintf-style format (only %s is supported) and arguments
 * to provide information about what broke and what you were
 * expecting.
 *
 * The invariant message will be stripped in production, but the invariant
 * will remain to ensure logic does not differ in production.
 */

var invariant = function(condition, format, a, b, c, d, e, f) {
  if (false) {
    if (format === undefined) {
      throw new Error('invariant requires an error message argument');
    }
  }

  if (!condition) {
    var error;
    if (format === undefined) {
      error = new Error(
        'Minified exception occurred; use the non-minified dev environment ' +
        'for the full error message and additional helpful warnings.'
      );
    } else {
      var args = [a, b, c, d, e, f];
      var argIndex = 0;
      error = new Error(
        'Invariant Violation: ' +
        format.replace(/%s/g, function() { return args[argIndex++]; })
      );
    }

    error.framesToPop = 1; // we don't care about invariant's own frame
    throw error;
  }
};

module.exports = invariant;

},{}],4:[function(require,module,exports){
/**
 * Copyright 2013-2014 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

"use strict";

/**
 * Constructs an enumeration with keys equal to their value.
 *
 * For example:
 *
 *   var COLORS = keyMirror({blue: null, red: null});
 *   var myColor = COLORS.blue;
 *   var isColorValid = !!COLORS[myColor];
 *
 * The last line could not be performed if the values of the generated enum were
 * not equal to their keys.
 *
 *   Input:  {key1: val1, key2: val2}
 *   Output: {key1: key1, key2: key2}
 *
 * @param {object} obj
 * @return {object}
 */
var keyMirror = function(obj) {
  var ret = {};
  var key;
  if (!(obj instanceof Object && !Array.isArray(obj))) {
    throw new Error('keyMirror(...): Argument must be an object.');
  }
  for (key in obj) {
    if (!obj.hasOwnProperty(key)) {
      continue;
    }
    ret[key] = key;
  }
  return ret;
};

module.exports = keyMirror;

},{}],5:[function(require,module,exports){
'use strict';

function ToObject(val) {
	if (val == null) {
		throw new TypeError('Object.assign cannot be called with null or undefined');
	}

	return Object(val);
}

module.exports = Object.assign || function (target, source) {
	var pendingException;
	var from;
	var keys;
	var to = ToObject(target);

	for (var s = 1; s < arguments.length; s++) {
		from = arguments[s];
		keys = Object.keys(Object(from));

		for (var i = 0; i < keys.length; i++) {
			try {
				to[keys[i]] = from[keys[i]];
			} catch (err) {
				if (pendingException === undefined) {
					pendingException = err;
				}
			}
		}
	}

	if (pendingException) {
		throw pendingException;
	}

	return to;
};

},{}],6:[function(require,module,exports){
// Copyright Joyent, Inc. and other Node contributors.
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to permit
// persons to whom the Software is furnished to do so, subject to the
// following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
// USE OR OTHER DEALINGS IN THE SOFTWARE.

function EventEmitter() {
  this._events = this._events || {};
  this._maxListeners = this._maxListeners || undefined;
}
module.exports = EventEmitter;

// Backwards-compat with node 0.10.x
EventEmitter.EventEmitter = EventEmitter;

EventEmitter.prototype._events = undefined;
EventEmitter.prototype._maxListeners = undefined;

// By default EventEmitters will print a warning if more than 10 listeners are
// added to it. This is a useful default which helps finding memory leaks.
EventEmitter.defaultMaxListeners = 10;

// Obviously not all Emitters should be limited to 10. This function allows
// that to be increased. Set to zero for unlimited.
EventEmitter.prototype.setMaxListeners = function(n) {
  if (!isNumber(n) || n < 0 || isNaN(n))
    throw TypeError('n must be a positive number');
  this._maxListeners = n;
  return this;
};

EventEmitter.prototype.emit = function(type) {
  var er, handler, len, args, i, listeners;

  if (!this._events)
    this._events = {};

  // If there is no 'error' event listener then throw.
  if (type === 'error') {
    if (!this._events.error ||
        (isObject(this._events.error) && !this._events.error.length)) {
      er = arguments[1];
      if (er instanceof Error) {
        throw er; // Unhandled 'error' event
      }
      throw TypeError('Uncaught, unspecified "error" event.');
    }
  }

  handler = this._events[type];

  if (isUndefined(handler))
    return false;

  if (isFunction(handler)) {
    switch (arguments.length) {
      // fast cases
      case 1:
        handler.call(this);
        break;
      case 2:
        handler.call(this, arguments[1]);
        break;
      case 3:
        handler.call(this, arguments[1], arguments[2]);
        break;
      // slower
      default:
        len = arguments.length;
        args = new Array(len - 1);
        for (i = 1; i < len; i++)
          args[i - 1] = arguments[i];
        handler.apply(this, args);
    }
  } else if (isObject(handler)) {
    len = arguments.length;
    args = new Array(len - 1);
    for (i = 1; i < len; i++)
      args[i - 1] = arguments[i];

    listeners = handler.slice();
    len = listeners.length;
    for (i = 0; i < len; i++)
      listeners[i].apply(this, args);
  }

  return true;
};

EventEmitter.prototype.addListener = function(type, listener) {
  var m;

  if (!isFunction(listener))
    throw TypeError('listener must be a function');

  if (!this._events)
    this._events = {};

  // To avoid recursion in the case that type === "newListener"! Before
  // adding it to the listeners, first emit "newListener".
  if (this._events.newListener)
    this.emit('newListener', type,
              isFunction(listener.listener) ?
              listener.listener : listener);

  if (!this._events[type])
    // Optimize the case of one listener. Don't need the extra array object.
    this._events[type] = listener;
  else if (isObject(this._events[type]))
    // If we've already got an array, just append.
    this._events[type].push(listener);
  else
    // Adding the second element, need to change to array.
    this._events[type] = [this._events[type], listener];

  // Check for listener leak
  if (isObject(this._events[type]) && !this._events[type].warned) {
    var m;
    if (!isUndefined(this._maxListeners)) {
      m = this._maxListeners;
    } else {
      m = EventEmitter.defaultMaxListeners;
    }

    if (m && m > 0 && this._events[type].length > m) {
      this._events[type].warned = true;
      console.error('(node) warning: possible EventEmitter memory ' +
                    'leak detected. %d listeners added. ' +
                    'Use emitter.setMaxListeners() to increase limit.',
                    this._events[type].length);
      if (typeof console.trace === 'function') {
        // not supported in IE 10
        console.trace();
      }
    }
  }

  return this;
};

EventEmitter.prototype.on = EventEmitter.prototype.addListener;

EventEmitter.prototype.once = function(type, listener) {
  if (!isFunction(listener))
    throw TypeError('listener must be a function');

  var fired = false;

  function g() {
    this.removeListener(type, g);

    if (!fired) {
      fired = true;
      listener.apply(this, arguments);
    }
  }

  g.listener = listener;
  this.on(type, g);

  return this;
};

// emits a 'removeListener' event iff the listener was removed
EventEmitter.prototype.removeListener = function(type, listener) {
  var list, position, length, i;

  if (!isFunction(listener))
    throw TypeError('listener must be a function');

  if (!this._events || !this._events[type])
    return this;

  list = this._events[type];
  length = list.length;
  position = -1;

  if (list === listener ||
      (isFunction(list.listener) && list.listener === listener)) {
    delete this._events[type];
    if (this._events.removeListener)
      this.emit('removeListener', type, listener);

  } else if (isObject(list)) {
    for (i = length; i-- > 0;) {
      if (list[i] === listener ||
          (list[i].listener && list[i].listener === listener)) {
        position = i;
        break;
      }
    }

    if (position < 0)
      return this;

    if (list.length === 1) {
      list.length = 0;
      delete this._events[type];
    } else {
      list.splice(position, 1);
    }

    if (this._events.removeListener)
      this.emit('removeListener', type, listener);
  }

  return this;
};

EventEmitter.prototype.removeAllListeners = function(type) {
  var key, listeners;

  if (!this._events)
    return this;

  // not listening for removeListener, no need to emit
  if (!this._events.removeListener) {
    if (arguments.length === 0)
      this._events = {};
    else if (this._events[type])
      delete this._events[type];
    return this;
  }

  // emit removeListener for all listeners on all events
  if (arguments.length === 0) {
    for (key in this._events) {
      if (key === 'removeListener') continue;
      this.removeAllListeners(key);
    }
    this.removeAllListeners('removeListener');
    this._events = {};
    return this;
  }

  listeners = this._events[type];

  if (isFunction(listeners)) {
    this.removeListener(type, listeners);
  } else {
    // LIFO order
    while (listeners.length)
      this.removeListener(type, listeners[listeners.length - 1]);
  }
  delete this._events[type];

  return this;
};

EventEmitter.prototype.listeners = function(type) {
  var ret;
  if (!this._events || !this._events[type])
    ret = [];
  else if (isFunction(this._events[type]))
    ret = [this._events[type]];
  else
    ret = this._events[type].slice();
  return ret;
};

EventEmitter.listenerCount = function(emitter, type) {
  var ret;
  if (!emitter._events || !emitter._events[type])
    ret = 0;
  else if (isFunction(emitter._events[type]))
    ret = 1;
  else
    ret = emitter._events[type].length;
  return ret;
};

function isFunction(arg) {
  return typeof arg === 'function';
}

function isNumber(arg) {
  return typeof arg === 'number';
}

function isObject(arg) {
  return typeof arg === 'object' && arg !== null;
}

function isUndefined(arg) {
  return arg === void 0;
}

},{}],7:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  ZÁVISLOSTI  ***********/
var ProfilePhoto = require('../components/profile').ProfilePhoto;
var MessageActions = require('../flux/actions/chat/MessageActionCreators');
var MessageStore = require('../flux/stores/chat/MessageStore');
var TimerFactory = require('../components/timer');/* je v cachi, nebude se vytvářet vícekrát */

/***********  NASTAVENÍ  ***********/

/** Odkazy ke komunikaci */
var reactSendMessage = document.getElementById('reactChatSendMessageLink');
var reactRefreshMessages = document.getElementById('reactChatRefreshMessagesLink');
var reactLoadMessages = document.getElementById('reactChatLoadMessagesLink');
var reactGetOlderMessages = document.getElementById('reactChatGetOlderMessagesLink');
/* k poslání zprávy*/
var reactSendMessageLink = reactSendMessage.href;
/* k pravidelnému dotazu na zprávy */
var reactRefreshMessagesLink = reactRefreshMessages.href;
/* k dotazu na načtení zpráv, když nemám zatím žádné (typicky poslední zprávy mezi uživateli) */
var reactLoadMessagesLink = reactLoadMessages.href;
/* k dotazu na starší zprávy */
var reactGetOlderMessagesLink = reactGetOlderMessages.href;
/** prefix před parametry do url */
var parametersPrefix = reactSendMessage.dataset.parprefix;
/** obvyklý počet příchozích zpráv v odpovědi u pravidelného a iniciálního požadavku (aneb kolik zpráv mi přijde, když jich je na serveru ještě dost) */
var usualOlderMessagesCount = reactGetOlderMessages.dataset.maxmessages;
var usualLoadMessagesCount = reactLoadMessages.dataset.maxmessages;
/* časovač pro pravidelné požadavky na server */
var Timer = TimerFactory.newInstance();

/***********  DEFINICE  ***********/
/** Část okna, která má svislý posuvník - obsahuje zprávy, tlačítko pro donačítání... */
var MessagesWindow = React.createClass({displayName: "MessagesWindow",
  getInitialState: function() {
    return {messages: [], infoMessages: [], thereIsMore: true, href: '' };
  },
  componentDidMount: function() {
    var component = this;
    MessageStore.addChangeListener(function(){
      component.setState(MessageStore.getState());
    });
    MessageActions.createGetInitialMessages(reactLoadMessagesLink, this.props.userCodedId, parametersPrefix, usualLoadMessagesCount);
  },
  render: function() {
    var messages = this.state.messages;
    var infoMessages = this.state.infoMessages;
    var oldestId = this.getOldestId(messages);
    var userCodedId = this.props.userCodedId;
    /* sestavení odkazu pro tlačítko */
    var moreButtonLink = reactGetOlderMessagesLink + '&' + parametersPrefix + 'lastId=' + oldestId + '&' + parametersPrefix + 'withUserId=' + this.props.userCodedId;
    return (
      React.createElement("div", {className: "messagesWindow"}, 
        React.createElement(LoadMoreButton, {loadHref: moreButtonLink, oldestId: oldestId, thereIsMore: this.state.thereIsMore, userCodedId: userCodedId}), 
        messages.map(function(message){
            return React.createElement(Message, {key: message.id, messageData: message, userHref: message.profileHref, profilePhotoUrl: message.profilePhotoUrl});
        }), 
        
        infoMessages.map(function(message, i){
              return React.createElement(InfoMessage, {key: userCodedId + 'info' + i, messageData: message});
          })
        
      )
    );
  },
  getOldestId: function(messages){
    return (messages[0]) ? messages[0].id : 9007199254740991; /*nastavení hodnoty nebo maximální hodnoty, když není*/
  }
});

var InfoMessage = React.createClass({displayName: "InfoMessage",
  render: function(){
      return(React.createElement("span", {className: "info-message"}, this.props.messageData.text));
  }
});

/** Jedna zpráva. */
var Message = React.createClass({displayName: "Message",
  render: function() {
    var message = this.props.messageData;
    return (
      React.createElement("div", {className: "message"}, 
        React.createElement(ProfilePhoto, {profileLink: this.props.userHref, userName: message.name, profilePhotoUrl: this.props.profilePhotoUrl}), 
        React.createElement("div", {className: "messageArrow"}), 
        React.createElement("p", {className: "messageText"}, 
          message.text, 
          React.createElement("span", {className: "messageDatetime"}, message.sendedDate)
        ), 
        React.createElement("div", {className: "clear"})
      )
    );
  }
});

/** Donačítací tlačítko */
var LoadMoreButton = React.createClass({displayName: "LoadMoreButton",
  render: function() {
    if(!this.props.thereIsMore){ return null;}
    return (
      React.createElement("span", {className: "loadMoreButton btn-main loadingbutton ui-btn", onClick: this.handleClick}, 
        "Načíst další zprávy"
      )
    );
  },
  handleClick: function(){
    MessageActions.createGetOlderMessages(reactGetOlderMessagesLink, this.props.userCodedId, this.props.oldestId, parametersPrefix, usualOlderMessagesCount);
  }
});

/** Formulář pro odesílání zpráv */
var NewMessageForm = React.createClass({displayName: "NewMessageForm",
  render: function() {
    var loggedUser = this.props.loggedUser;
    return (
      React.createElement("div", {className: "newMessage"}, 
        React.createElement(ProfilePhoto, {profileLink: loggedUser.href, userName: loggedUser.name, profilePhotoUrl: loggedUser.profilePhotoUrl}), 
        React.createElement("div", {className: "messageArrow"}), 
        React.createElement("form", {onSubmit: this.onSubmit}, 
          React.createElement("input", {type: "text", className: "messageInput"}), 
          React.createElement("input", {type: "submit", className: "btn-main medium button", value: "Odeslat"})
        )
      )
    );
  },
  onSubmit: function(e){/* Vezme zprávu ze submitu a pošle ji. Také smaže zprávu napsanou v inputu. */
    e.preventDefault();
    var input = e.target.getElementsByClassName('messageInput')[0];
    var message = input.value;
    if(message == undefined || message.trim() == '') return;
    input.value = '';
    MessageActions.createSendMessage(reactSendMessageLink, this.props.userCodedId, message);
  }
});

/**
 * inicializuje časovač pravidelně se dotazující na nové zprávy v závislosti na tom, jak se mění data v MessageStore
 * @param {string} userCodedId kódované id uživatele, se kterým si píšu
 */
var initializeChatTimer = function(userCodedId){
  MessageStore.addChangeListener(function(){
    var state = MessageStore.getState();
    if(state.dataVersion == 1){/* data se poprvé změnila */
      Timer.maximumInterval = 60000;
      Timer.initialInterval = 3000;
      Timer.intervalIncrase = 2000;
      Timer.lastId = (state.messages.length > 0) ? Timer.lastId = state.messages[state.messages.length - 1].id : 0;
      Timer.tick = function(){
        MessageActions.createRefreshMessages(reactRefreshMessagesLink, userCodedId, Timer.lastId, parametersPrefix);
      };
      Timer.start();
    }else{/* když se data nezměnila poprvé, ale určitě se změnila */
      Timer.lastId = (state.messages.length > 0) ? Timer.lastId = state.messages[state.messages.length - 1].id : 0;
      Timer.resetTime();
    }
  });

};

module.exports = {
  /** Okno celého chatu s jedním uživatelem */
  ChatWindow: React.createClass({displayName: "ChatWindow",
    componentDidMount: function() {
      initializeChatTimer(this.props.userCodedId);
      MessageActions.reloadWindowUnload();
    },
    render: function () {
      return (
        React.createElement("div", {className: "chatWindow"}, 
          React.createElement(MessagesWindow, {userCodedId: this.props.userCodedId}), 
          React.createElement(NewMessageForm, {loggedUser: this.props.loggedUser, userCodedId: this.props.userCodedId})
        )
      )
    }
  })
};

},{"../components/profile":8,"../components/timer":9,"../flux/actions/chat/MessageActionCreators":10,"../flux/stores/chat/MessageStore":13}],8:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */
module.exports = {

  /** Komponenta na profilovou fotku */
  ProfilePhoto: React.createClass({displayName: "ProfilePhoto",
    render: function () {
      return (
        React.createElement("a", {className: "generatedProfile", href: this.props.profileLink, title: this.props.userName}, 
          React.createElement("img", {src: this.props.profilePhotoUrl})
        )
      );
    }
  })

};

},{}],9:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 * Třída zajišťující pravidelné tiky
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */
/**/
/* Třída zajišťující pravidelné tiky, které se mohou s každým tiknutím prodlužovat */
function Timer() {
  /*
      !!! NEMĚŇTE TYTO PARAMETRY PŘÍMO V TOMTO SOUBORU, ZMĚŇTE JE U VAŠÍ INSTANCE TIMERU !!!
  */
  this.currentInterval = 1000; /* aktuální čekání mezi tiky */
  this.initialInterval = 1000; /* počáteční interval */
  this.intervalIncrase = 0;/* zvýšení intervalu po každém tiku */
  this.maximumInterval = 20000;/* maximální interval */
  this.running = false; /* indikátor, zda timer běží */
  this.tick = function(){};/* funkce, co se volá při každém tiku */
  this.start = function(){/* funkce, která spustí časovač */
    if(!this.running){
      this.running = true;
      this.resetTime();
      this.recursive();
    }
  };
  this.stop = function(){/* funkce, která timer zastaví*/
    this.running = false;
  };
  this.resetTime = function(){/* funkce, kterou vyresetuji čekání na počáteční hodnotu */
    this.currentInterval = this.initialInterval;
  };
  this.recursive = function(){/* nepřekrývat, funkce, která dělá smyčku */
    if(this.running){
      var timer = this;
      setTimeout(function(){
        timer.tick();
        timer.currentInterval = Math.min(timer.currentInterval + timer.intervalIncrase, timer.maximumInterval);
        timer.recursive();
      }, timer.currentInterval);
    }
  };

}

module.exports = {
  newInstance: function(){
    return new Timer();
  }
}

},{}],10:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 *
 * Tento soubor zastřešuje flux akce související se získáváním zpráv. Také zprostředkovává komunikaci se serverem.
 */

 var dispatcher = require('../../dispatcher/datenodeDispatcher');
 var constants = require('../../constants/ActionConstants');
 var EventEmitter = require('events').EventEmitter;

var ActionTypes = constants.ActionTypes;

module.exports = {  /**
   * Získá ze serveru posledních několik proběhlých zpráv s uživatelem s daným id
   * @param {string} url url, které se ptám na zprávy
   * @param {int} userCodedId kódované id uživatele, se kterým si píšu
   * @param {string} parametersPrefix prefix před parametry v url
   * @param {int} usualLoadMessagesCount  obvyklý počet příchozích zpráv v odpovědi
   */
  createGetInitialMessages: function(url, userCodedId, parametersPrefix, usualLoadMessagesCount){
    var data = {};
  	data[parametersPrefix + 'fromId'] = userCodedId;
    this.blockWindowUnload('Ještě se načítají zprávy, opravdu chcete odejít?');
    var exportObject = this;
    $.getJSON(url, data, function(result){
        if(result.length == 0) {
          dispatcher.dispatch({
            type: ActionTypes.NO_INITIAL_MESSAGES_ARRIVED
          });
        }else{
          dispatcher.dispatch({
            type: ActionTypes.OLDER_MESSAGES_ARRIVED,
            data: result,
            userCodedId : userCodedId,
            usualMessagesCount : usualLoadMessagesCount
            /* tady bych případně přidal další data */
          });
        }
    }).done(function() {
      exportObject.reloadWindowUnload();
    });
  },

  /**
   * Získá ze serveru několik starších zpráv
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {int}   oldestId id nejstarší zprávy (nejmenší známé id)
   * @param  {string} parametersPrefix prefix před parametry v url
   * @param {int} usualOlderMessagesCount  obvyklý počet příchozích zpráv v odpovědi
   */
  createGetOlderMessages: function(url, userCodedId, oldestId, parametersPrefix, usualOlderMessagesCount){
    var data = {};
  	data[parametersPrefix + 'lastId'] = oldestId;
    data[parametersPrefix + 'withUserId'] = userCodedId;
    $.getJSON(url, data, function(result){
        if(result.length == 0) return;
        dispatcher.dispatch({
          type: ActionTypes.OLDER_MESSAGES_ARRIVED,
          data: result,
          userCodedId : userCodedId,
          oldersId : oldestId,
          usualMessagesCount : usualOlderMessagesCount
        });
    });
  },

  /**
   * Pošle na server zprávu.
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {String} message text zprávy
   */
  createSendMessage: function(url, userCodedId, message){
    var data = {
      to: userCodedId,
      type: 'textMessage',
      text: message
    };
    this.blockWindowUnload('Zpráva se stále odesílá, prosíme počkejte několik sekund a pak to zkuste znova.');
    var exportObject = this;
    var json = JSON.stringify(data);
  		$.ajax({
  			dataType: "json",
  			type: 'POST',
  			url: url,
  			data: json,
  			contentType: 'application/json; charset=utf-8',
  			success: function(result){
          dispatcher.dispatch({
            type: ActionTypes.NEW_MESSAGES_ARRIVED,
            data: result,
            userCodedId : userCodedId
          });
        },
        complete: function(){
          exportObject.reloadWindowUnload();
        }
  		});
  },

  /**
   * Zeptá se serveru na nové zprávy
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {int} lastId poslední známé id
   * @param  {string} parametersPrefix prefix před parametry v url
   */
  createRefreshMessages: function(url, userCodedId, lastId, parametersPrefix){
    var data = {};
  	data[parametersPrefix + 'lastid'] = lastId;
    $.getJSON(url, data, function(result){
        if(result.length == 0) return;
        dispatcher.dispatch({
          type: ActionTypes.NEW_MESSAGES_ARRIVED,
          data: result,
          userCodedId : userCodedId
        });
    });
  },

  /**
  	 * Při pokusu zavřít nebo obnovit okno se zeptá uživatele,
  	 * zda chce okno skutečně zavřít/obnovit. Toto dělá v každém případě, dokud
  	 * se nezavolá reloadWindowUnload
  	 * @param {String} reason důvod uvedený v dialogu
  	 */
  	blockWindowUnload: function(reason) {
  		window.onbeforeunload = function () {
  			return reason;
  		};
  	},

  	/**
  	 * Vypne hlídání zavření/obnovení okna a vrátí jej do počátečního stavu.
  	 */
  	reloadWindowUnload: function() {
  		window.onbeforeunload = function () {
  			var unsend = false;
  			$.each($(".messageInput"), function () {//projde vsechny textarea chatu
  				if ($.trim($(this).val())) {//u kazdeho zkouma hodnotu bez whitespacu
  					unsend = true;
  				}
  			});
  			if (unsend) {
  				return 'Máte rozepsaný příspěvek. Chcete tuto stránku přesto opustit?';
  				/* hláška, co se objeví při pokusu obnovit/zavřít okno, zatímco má uživatel rozepsanou zprávu */
  			}
  		};
  	}
};

},{"../../constants/ActionConstants":11,"../../dispatcher/datenodeDispatcher":12,"events":6}],11:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */


var keyMirror = require('keymirror');

module.exports = {

  /* typy akcí, které mohou nastat */
  ActionTypes: keyMirror({
    /* CHAT */
    NO_INITIAL_MESSAGES_ARRIVED : null,/* přišla odpověď při prvotním načítání zpráv, ale byla prázdná*/
    OLDER_MESSAGES_ARRIVED : null,/* přišly starší (donačtené tlačítkem) zprávy */
    NEW_MESSAGES_ARRIVED : null/* přišly nové zprávy*/
  })

};

},{"keymirror":4}],12:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

var Dispatcher = require('flux').Dispatcher;

module.exports = new Dispatcher();

},{"flux":1}],13:[function(require,module,exports){
/**
 * This file is provided by Facebook for testing and evaluation purposes
 * only. Facebook reserves all rights not expressly granted.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * FACEBOOK BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 * AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

var Dispatcher = require('../../dispatcher/datenodeDispatcher');
var constants = require('../../constants/ActionConstants');
var EventEmitter = require('events').EventEmitter;
var assign = require('object-assign');

var CHANGE_EVENT = 'change';

var _dataVersion = 0;/* kolikrát se už změnila data */
var _messages = [];
var _infoMessages = [];
var _thereIsMore = true;

var MessageStore = assign({}, EventEmitter.prototype, {
  /* trigger změny */
  emitChange: function() {
    _dataVersion++;
    filterInfoMessages();
    this.emit(CHANGE_EVENT);
  },
  /* touto metodou lze pověsit listener reagující při změně*/
  addChangeListener: function(callback) {
    this.on(CHANGE_EVENT, callback);
  },
  /* touto metodou lze listener odejmout*/
  removeChangeListener: function(callback) {
    this.removeListener(CHANGE_EVENT, callback);
  },
  /* vrací stav zpráv v jediném objektu*/
  getState: function() {
    return {
      messages: _messages,
      infoMessages: _infoMessages,
      thereIsMore: _thereIsMore,
      dataVersion: _dataVersion
    };
  }

});

MessageStore.dispatchToken = Dispatcher.register(function(action) {
  var types = constants.ActionTypes;
  switch(action.type){
    case types.NEW_MESSAGES_ARRIVED :
      appendDataIntoMessages(action.userCodedId, action.data, action.usualMessagesCount);
      MessageStore.emitChange();
      break;
    case types.OLDER_MESSAGES_ARRIVED :
      prependDataIntoMessages(action.userCodedId, action.data, action.usualMessagesCount);
      MessageStore.emitChange();
      break;
    case types.NO_INITIAL_MESSAGES_ARRIVED:
      MessageStore.emitChange();/* když nepřijdou žádné zprávy při inicializaci, dá to najevo */
      break;
  }
});

/**
 * Nastaví zprávy ze standardního JSONu chatu (viz dokumentace) do stavu tohoto Store za existující zprávy.
 * @param  {int} userCodedId id uživatele, od kterého chci načíst zprávy
 * @param  {json} jsonData  data ze serveru
 */
var appendDataIntoMessages = function(userCodedId, jsonData){
  var result = jsonData[userCodedId];
  _messages = _messages.concat(result.messages);
};

/**
 * Nastaví zprávy ze standardního JSONu chatu (viz dokumentace) do stavu tohoto Store před existující zprávy.
 * @param  {int} userCodedId id uživatele, od kterého chci načíst zprávy
 * @param  {json} jsonData  data ze serveru
 * @param  {int} usualMessagesCount obvyklý počet zpráv - pokud je dodržen, zahodí nejstarší zprávu (pokud je zpráv dostatek)
 * a komponentě podle toho nastaví stav, že na serveru ještě jsou/už nejsou další zprávy
 */
var prependDataIntoMessages = function(userCodedId, jsonData, usualMessagesCount){
  var thereIsMore = true;
  var result = jsonData[userCodedId];
  if(result.messages.length < usualMessagesCount){/* pokud mám méně zpráv než je obvyklé*/
    thereIsMore = false;
  }else{
    result.messages.shift();/* odeberu první zprávu */
  }
  _thereIsMore = thereIsMore;
  _messages = result.messages.concat(_messages);
};

/**
 * Odfiltruje z dat infozprávy a vytřídí je zvlášť
 * @return {[type]} [description]
 */
var filterInfoMessages = function(){
  var clearMode = false; /* po přepnutí do čištění jen vyhazuje infozprávy*/
  for(var i = _messages.length - 1 ; i >= Math.max(0, _messages.length - 20); i--){/*projde zprávy shora dolů - jen posledních dvacet kvůli výkonu*/
    if(_messages[i].type == 1){/* když je to infozpráva */
      if(!clearMode){/* nečistí se */
        addToInfoMessages(_messages[i]);
      }
      _messages.splice(i,1);/* odstranění zprávy */
    }else{
      clearMode = true;/* když najde normální zprávu, vyčistí všechny infozprávy výše */
    }
  }
};

/**
 * Přidá zprávu k infozprávám, pokud mezi nimi ještě není
 * @param  {json} message zpráva přijatá ze serveru
 */
var addToInfoMessages = function(message) {
  var alreadyExists = false;
  _infoMessages.forEach(function(infoMessage){
    if(infoMessage.text == message.text){
      alreadyExists = true;
      return;
    }
  });
  if(!alreadyExists){
    _infoMessages.push(message);
  }
};

module.exports = MessageStore;

},{"../../constants/ActionConstants":11,"../../dispatcher/datenodeDispatcher":12,"events":6,"object-assign":5}],14:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  INICIALIZACE  ***********/
var chatRoot = document.getElementById('reactChatWindow');
if(typeof(chatRoot) != 'undefined' && chatRoot != null){/*existuje element pro chat*/
  var Chat = require('./chat/reactChat');
  var loggedUser = {
    name: chatRoot.dataset.username,
    href: chatRoot.dataset.userhref,
    profilePhotoUrl: chatRoot.dataset.profilephotourl
  };
  React.render(
      React.createElement(Chat.ChatWindow, {userCodedId: chatRoot.dataset.userinchatcodedid, loggedUser: loggedUser}),
      chatRoot
  );
}

},{"./chat/reactChat":7}]},{},[14])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwibm9kZV9tb2R1bGVzL2ZsdXgvaW5kZXguanMiLCJub2RlX21vZHVsZXMvZmx1eC9saWIvRGlzcGF0Y2hlci5qcyIsIm5vZGVfbW9kdWxlcy9mbHV4L2xpYi9pbnZhcmlhbnQuanMiLCJub2RlX21vZHVsZXMva2V5bWlycm9yL2luZGV4LmpzIiwibm9kZV9tb2R1bGVzL29iamVjdC1hc3NpZ24vaW5kZXguanMiLCJub2RlX21vZHVsZXMvd2F0Y2hpZnkvbm9kZV9tb2R1bGVzL2Jyb3dzZXJpZnkvbm9kZV9tb2R1bGVzL2V2ZW50cy9ldmVudHMuanMiLCJzcmMvY2hhdC9yZWFjdENoYXQuanMiLCJzcmMvY29tcG9uZW50cy9wcm9maWxlLmpzIiwic3JjL2NvbXBvbmVudHMvdGltZXIuanMiLCJzcmMvZmx1eC9hY3Rpb25zL2NoYXQvTWVzc2FnZUFjdGlvbkNyZWF0b3JzLmpzIiwic3JjL2ZsdXgvY29uc3RhbnRzL0FjdGlvbkNvbnN0YW50cy5qcyIsInNyYy9mbHV4L2Rpc3BhdGNoZXIvZGF0ZW5vZGVEaXNwYXRjaGVyLmpzIiwic3JjL2ZsdXgvc3RvcmVzL2NoYXQvTWVzc2FnZVN0b3JlLmpzIiwic3JjL3JlYWN0RGF0ZW5vZGUuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1ZBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDMVBBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNyREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzdTQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwTEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbERBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN4SkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcklBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8qKlxyXG4gKiBDb3B5cmlnaHQgKGMpIDIwMTQtMjAxNSwgRmFjZWJvb2ssIEluYy5cclxuICogQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICpcclxuICogVGhpcyBzb3VyY2UgY29kZSBpcyBsaWNlbnNlZCB1bmRlciB0aGUgQlNELXN0eWxlIGxpY2Vuc2UgZm91bmQgaW4gdGhlXHJcbiAqIExJQ0VOU0UgZmlsZSBpbiB0aGUgcm9vdCBkaXJlY3Rvcnkgb2YgdGhpcyBzb3VyY2UgdHJlZS4gQW4gYWRkaXRpb25hbCBncmFudFxyXG4gKiBvZiBwYXRlbnQgcmlnaHRzIGNhbiBiZSBmb3VuZCBpbiB0aGUgUEFURU5UUyBmaWxlIGluIHRoZSBzYW1lIGRpcmVjdG9yeS5cclxuICovXHJcblxyXG5tb2R1bGUuZXhwb3J0cy5EaXNwYXRjaGVyID0gcmVxdWlyZSgnLi9saWIvRGlzcGF0Y2hlcicpXHJcbiIsIi8qXHJcbiAqIENvcHlyaWdodCAoYykgMjAxNCwgRmFjZWJvb2ssIEluYy5cclxuICogQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICpcclxuICogVGhpcyBzb3VyY2UgY29kZSBpcyBsaWNlbnNlZCB1bmRlciB0aGUgQlNELXN0eWxlIGxpY2Vuc2UgZm91bmQgaW4gdGhlXHJcbiAqIExJQ0VOU0UgZmlsZSBpbiB0aGUgcm9vdCBkaXJlY3Rvcnkgb2YgdGhpcyBzb3VyY2UgdHJlZS4gQW4gYWRkaXRpb25hbCBncmFudFxyXG4gKiBvZiBwYXRlbnQgcmlnaHRzIGNhbiBiZSBmb3VuZCBpbiB0aGUgUEFURU5UUyBmaWxlIGluIHRoZSBzYW1lIGRpcmVjdG9yeS5cclxuICpcclxuICogQHByb3ZpZGVzTW9kdWxlIERpc3BhdGNoZXJcclxuICogQHR5cGVjaGVja3NcclxuICovXHJcblxyXG5cInVzZSBzdHJpY3RcIjtcclxuXHJcbnZhciBpbnZhcmlhbnQgPSByZXF1aXJlKCcuL2ludmFyaWFudCcpO1xyXG5cclxudmFyIF9sYXN0SUQgPSAxO1xyXG52YXIgX3ByZWZpeCA9ICdJRF8nO1xyXG5cclxuLyoqXHJcbiAqIERpc3BhdGNoZXIgaXMgdXNlZCB0byBicm9hZGNhc3QgcGF5bG9hZHMgdG8gcmVnaXN0ZXJlZCBjYWxsYmFja3MuIFRoaXMgaXNcclxuICogZGlmZmVyZW50IGZyb20gZ2VuZXJpYyBwdWItc3ViIHN5c3RlbXMgaW4gdHdvIHdheXM6XHJcbiAqXHJcbiAqICAgMSkgQ2FsbGJhY2tzIGFyZSBub3Qgc3Vic2NyaWJlZCB0byBwYXJ0aWN1bGFyIGV2ZW50cy4gRXZlcnkgcGF5bG9hZCBpc1xyXG4gKiAgICAgIGRpc3BhdGNoZWQgdG8gZXZlcnkgcmVnaXN0ZXJlZCBjYWxsYmFjay5cclxuICogICAyKSBDYWxsYmFja3MgY2FuIGJlIGRlZmVycmVkIGluIHdob2xlIG9yIHBhcnQgdW50aWwgb3RoZXIgY2FsbGJhY2tzIGhhdmVcclxuICogICAgICBiZWVuIGV4ZWN1dGVkLlxyXG4gKlxyXG4gKiBGb3IgZXhhbXBsZSwgY29uc2lkZXIgdGhpcyBoeXBvdGhldGljYWwgZmxpZ2h0IGRlc3RpbmF0aW9uIGZvcm0sIHdoaWNoXHJcbiAqIHNlbGVjdHMgYSBkZWZhdWx0IGNpdHkgd2hlbiBhIGNvdW50cnkgaXMgc2VsZWN0ZWQ6XHJcbiAqXHJcbiAqICAgdmFyIGZsaWdodERpc3BhdGNoZXIgPSBuZXcgRGlzcGF0Y2hlcigpO1xyXG4gKlxyXG4gKiAgIC8vIEtlZXBzIHRyYWNrIG9mIHdoaWNoIGNvdW50cnkgaXMgc2VsZWN0ZWRcclxuICogICB2YXIgQ291bnRyeVN0b3JlID0ge2NvdW50cnk6IG51bGx9O1xyXG4gKlxyXG4gKiAgIC8vIEtlZXBzIHRyYWNrIG9mIHdoaWNoIGNpdHkgaXMgc2VsZWN0ZWRcclxuICogICB2YXIgQ2l0eVN0b3JlID0ge2NpdHk6IG51bGx9O1xyXG4gKlxyXG4gKiAgIC8vIEtlZXBzIHRyYWNrIG9mIHRoZSBiYXNlIGZsaWdodCBwcmljZSBvZiB0aGUgc2VsZWN0ZWQgY2l0eVxyXG4gKiAgIHZhciBGbGlnaHRQcmljZVN0b3JlID0ge3ByaWNlOiBudWxsfVxyXG4gKlxyXG4gKiBXaGVuIGEgdXNlciBjaGFuZ2VzIHRoZSBzZWxlY3RlZCBjaXR5LCB3ZSBkaXNwYXRjaCB0aGUgcGF5bG9hZDpcclxuICpcclxuICogICBmbGlnaHREaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICogICAgIGFjdGlvblR5cGU6ICdjaXR5LXVwZGF0ZScsXHJcbiAqICAgICBzZWxlY3RlZENpdHk6ICdwYXJpcydcclxuICogICB9KTtcclxuICpcclxuICogVGhpcyBwYXlsb2FkIGlzIGRpZ2VzdGVkIGJ5IGBDaXR5U3RvcmVgOlxyXG4gKlxyXG4gKiAgIGZsaWdodERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gKiAgICAgaWYgKHBheWxvYWQuYWN0aW9uVHlwZSA9PT0gJ2NpdHktdXBkYXRlJykge1xyXG4gKiAgICAgICBDaXR5U3RvcmUuY2l0eSA9IHBheWxvYWQuc2VsZWN0ZWRDaXR5O1xyXG4gKiAgICAgfVxyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBXaGVuIHRoZSB1c2VyIHNlbGVjdHMgYSBjb3VudHJ5LCB3ZSBkaXNwYXRjaCB0aGUgcGF5bG9hZDpcclxuICpcclxuICogICBmbGlnaHREaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICogICAgIGFjdGlvblR5cGU6ICdjb3VudHJ5LXVwZGF0ZScsXHJcbiAqICAgICBzZWxlY3RlZENvdW50cnk6ICdhdXN0cmFsaWEnXHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoaXMgcGF5bG9hZCBpcyBkaWdlc3RlZCBieSBib3RoIHN0b3JlczpcclxuICpcclxuICogICAgQ291bnRyeVN0b3JlLmRpc3BhdGNoVG9rZW4gPSBmbGlnaHREaXNwYXRjaGVyLnJlZ2lzdGVyKGZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICogICAgIGlmIChwYXlsb2FkLmFjdGlvblR5cGUgPT09ICdjb3VudHJ5LXVwZGF0ZScpIHtcclxuICogICAgICAgQ291bnRyeVN0b3JlLmNvdW50cnkgPSBwYXlsb2FkLnNlbGVjdGVkQ291bnRyeTtcclxuICogICAgIH1cclxuICogICB9KTtcclxuICpcclxuICogV2hlbiB0aGUgY2FsbGJhY2sgdG8gdXBkYXRlIGBDb3VudHJ5U3RvcmVgIGlzIHJlZ2lzdGVyZWQsIHdlIHNhdmUgYSByZWZlcmVuY2VcclxuICogdG8gdGhlIHJldHVybmVkIHRva2VuLiBVc2luZyB0aGlzIHRva2VuIHdpdGggYHdhaXRGb3IoKWAsIHdlIGNhbiBndWFyYW50ZWVcclxuICogdGhhdCBgQ291bnRyeVN0b3JlYCBpcyB1cGRhdGVkIGJlZm9yZSB0aGUgY2FsbGJhY2sgdGhhdCB1cGRhdGVzIGBDaXR5U3RvcmVgXHJcbiAqIG5lZWRzIHRvIHF1ZXJ5IGl0cyBkYXRhLlxyXG4gKlxyXG4gKiAgIENpdHlTdG9yZS5kaXNwYXRjaFRva2VuID0gZmxpZ2h0RGlzcGF0Y2hlci5yZWdpc3RlcihmdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAqICAgICBpZiAocGF5bG9hZC5hY3Rpb25UeXBlID09PSAnY291bnRyeS11cGRhdGUnKSB7XHJcbiAqICAgICAgIC8vIGBDb3VudHJ5U3RvcmUuY291bnRyeWAgbWF5IG5vdCBiZSB1cGRhdGVkLlxyXG4gKiAgICAgICBmbGlnaHREaXNwYXRjaGVyLndhaXRGb3IoW0NvdW50cnlTdG9yZS5kaXNwYXRjaFRva2VuXSk7XHJcbiAqICAgICAgIC8vIGBDb3VudHJ5U3RvcmUuY291bnRyeWAgaXMgbm93IGd1YXJhbnRlZWQgdG8gYmUgdXBkYXRlZC5cclxuICpcclxuICogICAgICAgLy8gU2VsZWN0IHRoZSBkZWZhdWx0IGNpdHkgZm9yIHRoZSBuZXcgY291bnRyeVxyXG4gKiAgICAgICBDaXR5U3RvcmUuY2l0eSA9IGdldERlZmF1bHRDaXR5Rm9yQ291bnRyeShDb3VudHJ5U3RvcmUuY291bnRyeSk7XHJcbiAqICAgICB9XHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoZSB1c2FnZSBvZiBgd2FpdEZvcigpYCBjYW4gYmUgY2hhaW5lZCwgZm9yIGV4YW1wbGU6XHJcbiAqXHJcbiAqICAgRmxpZ2h0UHJpY2VTdG9yZS5kaXNwYXRjaFRva2VuID1cclxuICogICAgIGZsaWdodERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gKiAgICAgICBzd2l0Y2ggKHBheWxvYWQuYWN0aW9uVHlwZSkge1xyXG4gKiAgICAgICAgIGNhc2UgJ2NvdW50cnktdXBkYXRlJzpcclxuICogICAgICAgICAgIGZsaWdodERpc3BhdGNoZXIud2FpdEZvcihbQ2l0eVN0b3JlLmRpc3BhdGNoVG9rZW5dKTtcclxuICogICAgICAgICAgIEZsaWdodFByaWNlU3RvcmUucHJpY2UgPVxyXG4gKiAgICAgICAgICAgICBnZXRGbGlnaHRQcmljZVN0b3JlKENvdW50cnlTdG9yZS5jb3VudHJ5LCBDaXR5U3RvcmUuY2l0eSk7XHJcbiAqICAgICAgICAgICBicmVhaztcclxuICpcclxuICogICAgICAgICBjYXNlICdjaXR5LXVwZGF0ZSc6XHJcbiAqICAgICAgICAgICBGbGlnaHRQcmljZVN0b3JlLnByaWNlID1cclxuICogICAgICAgICAgICAgRmxpZ2h0UHJpY2VTdG9yZShDb3VudHJ5U3RvcmUuY291bnRyeSwgQ2l0eVN0b3JlLmNpdHkpO1xyXG4gKiAgICAgICAgICAgYnJlYWs7XHJcbiAqICAgICB9XHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoZSBgY291bnRyeS11cGRhdGVgIHBheWxvYWQgd2lsbCBiZSBndWFyYW50ZWVkIHRvIGludm9rZSB0aGUgc3RvcmVzJ1xyXG4gKiByZWdpc3RlcmVkIGNhbGxiYWNrcyBpbiBvcmRlcjogYENvdW50cnlTdG9yZWAsIGBDaXR5U3RvcmVgLCB0aGVuXHJcbiAqIGBGbGlnaHRQcmljZVN0b3JlYC5cclxuICovXHJcblxyXG4gIGZ1bmN0aW9uIERpc3BhdGNoZXIoKSB7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrcyA9IHt9O1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmcgPSB7fTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNIYW5kbGVkID0ge307XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmcgPSBmYWxzZTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQgPSBudWxsO1xyXG4gIH1cclxuXHJcbiAgLyoqXHJcbiAgICogUmVnaXN0ZXJzIGEgY2FsbGJhY2sgdG8gYmUgaW52b2tlZCB3aXRoIGV2ZXJ5IGRpc3BhdGNoZWQgcGF5bG9hZC4gUmV0dXJuc1xyXG4gICAqIGEgdG9rZW4gdGhhdCBjYW4gYmUgdXNlZCB3aXRoIGB3YWl0Rm9yKClgLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtmdW5jdGlvbn0gY2FsbGJhY2tcclxuICAgKiBAcmV0dXJuIHtzdHJpbmd9XHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUucmVnaXN0ZXI9ZnVuY3Rpb24oY2FsbGJhY2spIHtcclxuICAgIHZhciBpZCA9IF9wcmVmaXggKyBfbGFzdElEKys7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0gPSBjYWxsYmFjaztcclxuICAgIHJldHVybiBpZDtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBSZW1vdmVzIGEgY2FsbGJhY2sgYmFzZWQgb24gaXRzIHRva2VuLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IGlkXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUudW5yZWdpc3Rlcj1mdW5jdGlvbihpZCkge1xyXG4gICAgaW52YXJpYW50KFxyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0sXHJcbiAgICAgICdEaXNwYXRjaGVyLnVucmVnaXN0ZXIoLi4uKTogYCVzYCBkb2VzIG5vdCBtYXAgdG8gYSByZWdpc3RlcmVkIGNhbGxiYWNrLicsXHJcbiAgICAgIGlkXHJcbiAgICApO1xyXG4gICAgZGVsZXRlIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBXYWl0cyBmb3IgdGhlIGNhbGxiYWNrcyBzcGVjaWZpZWQgdG8gYmUgaW52b2tlZCBiZWZvcmUgY29udGludWluZyBleGVjdXRpb25cclxuICAgKiBvZiB0aGUgY3VycmVudCBjYWxsYmFjay4gVGhpcyBtZXRob2Qgc2hvdWxkIG9ubHkgYmUgdXNlZCBieSBhIGNhbGxiYWNrIGluXHJcbiAgICogcmVzcG9uc2UgdG8gYSBkaXNwYXRjaGVkIHBheWxvYWQuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge2FycmF5PHN0cmluZz59IGlkc1xyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLndhaXRGb3I9ZnVuY3Rpb24oaWRzKSB7XHJcbiAgICBpbnZhcmlhbnQoXHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyxcclxuICAgICAgJ0Rpc3BhdGNoZXIud2FpdEZvciguLi4pOiBNdXN0IGJlIGludm9rZWQgd2hpbGUgZGlzcGF0Y2hpbmcuJ1xyXG4gICAgKTtcclxuICAgIGZvciAodmFyIGlpID0gMDsgaWkgPCBpZHMubGVuZ3RoOyBpaSsrKSB7XHJcbiAgICAgIHZhciBpZCA9IGlkc1tpaV07XHJcbiAgICAgIGlmICh0aGlzLiREaXNwYXRjaGVyX2lzUGVuZGluZ1tpZF0pIHtcclxuICAgICAgICBpbnZhcmlhbnQoXHJcbiAgICAgICAgICB0aGlzLiREaXNwYXRjaGVyX2lzSGFuZGxlZFtpZF0sXHJcbiAgICAgICAgICAnRGlzcGF0Y2hlci53YWl0Rm9yKC4uLik6IENpcmN1bGFyIGRlcGVuZGVuY3kgZGV0ZWN0ZWQgd2hpbGUgJyArXHJcbiAgICAgICAgICAnd2FpdGluZyBmb3IgYCVzYC4nLFxyXG4gICAgICAgICAgaWRcclxuICAgICAgICApO1xyXG4gICAgICAgIGNvbnRpbnVlO1xyXG4gICAgICB9XHJcbiAgICAgIGludmFyaWFudChcclxuICAgICAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0sXHJcbiAgICAgICAgJ0Rpc3BhdGNoZXIud2FpdEZvciguLi4pOiBgJXNgIGRvZXMgbm90IG1hcCB0byBhIHJlZ2lzdGVyZWQgY2FsbGJhY2suJyxcclxuICAgICAgICBpZFxyXG4gICAgICApO1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2ludm9rZUNhbGxiYWNrKGlkKTtcclxuICAgIH1cclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBEaXNwYXRjaGVzIGEgcGF5bG9hZCB0byBhbGwgcmVnaXN0ZXJlZCBjYWxsYmFja3MuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge29iamVjdH0gcGF5bG9hZFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLmRpc3BhdGNoPWZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICAgIGludmFyaWFudChcclxuICAgICAgIXRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyxcclxuICAgICAgJ0Rpc3BhdGNoLmRpc3BhdGNoKC4uLik6IENhbm5vdCBkaXNwYXRjaCBpbiB0aGUgbWlkZGxlIG9mIGEgZGlzcGF0Y2guJ1xyXG4gICAgKTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfc3RhcnREaXNwYXRjaGluZyhwYXlsb2FkKTtcclxuICAgIHRyeSB7XHJcbiAgICAgIGZvciAodmFyIGlkIGluIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzKSB7XHJcbiAgICAgICAgaWYgKHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nW2lkXSkge1xyXG4gICAgICAgICAgY29udGludWU7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHRoaXMuJERpc3BhdGNoZXJfaW52b2tlQ2FsbGJhY2soaWQpO1xyXG4gICAgICB9XHJcbiAgICB9IGZpbmFsbHkge1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX3N0b3BEaXNwYXRjaGluZygpO1xyXG4gICAgfVxyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIElzIHRoaXMgRGlzcGF0Y2hlciBjdXJyZW50bHkgZGlzcGF0Y2hpbmcuXHJcbiAgICpcclxuICAgKiBAcmV0dXJuIHtib29sZWFufVxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLmlzRGlzcGF0Y2hpbmc9ZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4gdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIENhbGwgdGhlIGNhbGxiYWNrIHN0b3JlZCB3aXRoIHRoZSBnaXZlbiBpZC4gQWxzbyBkbyBzb21lIGludGVybmFsXHJcbiAgICogYm9va2tlZXBpbmcuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge3N0cmluZ30gaWRcclxuICAgKiBAaW50ZXJuYWxcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS4kRGlzcGF0Y2hlcl9pbnZva2VDYWxsYmFjaz1mdW5jdGlvbihpZCkge1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmdbaWRdID0gdHJ1ZTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXSh0aGlzLiREaXNwYXRjaGVyX3BlbmRpbmdQYXlsb2FkKTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNIYW5kbGVkW2lkXSA9IHRydWU7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogU2V0IHVwIGJvb2trZWVwaW5nIG5lZWRlZCB3aGVuIGRpc3BhdGNoaW5nLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtvYmplY3R9IHBheWxvYWRcclxuICAgKiBAaW50ZXJuYWxcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS4kRGlzcGF0Y2hlcl9zdGFydERpc3BhdGNoaW5nPWZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICAgIGZvciAodmFyIGlkIGluIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzKSB7XHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nW2lkXSA9IGZhbHNlO1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2lzSGFuZGxlZFtpZF0gPSBmYWxzZTtcclxuICAgIH1cclxuICAgIHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQgPSBwYXlsb2FkO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nID0gdHJ1ZTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBDbGVhciBib29ra2VlcGluZyB1c2VkIGZvciBkaXNwYXRjaGluZy5cclxuICAgKlxyXG4gICAqIEBpbnRlcm5hbFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLiREaXNwYXRjaGVyX3N0b3BEaXNwYXRjaGluZz1mdW5jdGlvbigpIHtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQgPSBudWxsO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nID0gZmFsc2U7XHJcbiAgfTtcclxuXHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IERpc3BhdGNoZXI7XHJcbiIsIi8qKlxyXG4gKiBDb3B5cmlnaHQgKGMpIDIwMTQsIEZhY2Vib29rLCBJbmMuXHJcbiAqIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqXHJcbiAqIFRoaXMgc291cmNlIGNvZGUgaXMgbGljZW5zZWQgdW5kZXIgdGhlIEJTRC1zdHlsZSBsaWNlbnNlIGZvdW5kIGluIHRoZVxyXG4gKiBMSUNFTlNFIGZpbGUgaW4gdGhlIHJvb3QgZGlyZWN0b3J5IG9mIHRoaXMgc291cmNlIHRyZWUuIEFuIGFkZGl0aW9uYWwgZ3JhbnRcclxuICogb2YgcGF0ZW50IHJpZ2h0cyBjYW4gYmUgZm91bmQgaW4gdGhlIFBBVEVOVFMgZmlsZSBpbiB0aGUgc2FtZSBkaXJlY3RvcnkuXHJcbiAqXHJcbiAqIEBwcm92aWRlc01vZHVsZSBpbnZhcmlhbnRcclxuICovXHJcblxyXG5cInVzZSBzdHJpY3RcIjtcclxuXHJcbi8qKlxyXG4gKiBVc2UgaW52YXJpYW50KCkgdG8gYXNzZXJ0IHN0YXRlIHdoaWNoIHlvdXIgcHJvZ3JhbSBhc3N1bWVzIHRvIGJlIHRydWUuXHJcbiAqXHJcbiAqIFByb3ZpZGUgc3ByaW50Zi1zdHlsZSBmb3JtYXQgKG9ubHkgJXMgaXMgc3VwcG9ydGVkKSBhbmQgYXJndW1lbnRzXHJcbiAqIHRvIHByb3ZpZGUgaW5mb3JtYXRpb24gYWJvdXQgd2hhdCBicm9rZSBhbmQgd2hhdCB5b3Ugd2VyZVxyXG4gKiBleHBlY3RpbmcuXHJcbiAqXHJcbiAqIFRoZSBpbnZhcmlhbnQgbWVzc2FnZSB3aWxsIGJlIHN0cmlwcGVkIGluIHByb2R1Y3Rpb24sIGJ1dCB0aGUgaW52YXJpYW50XHJcbiAqIHdpbGwgcmVtYWluIHRvIGVuc3VyZSBsb2dpYyBkb2VzIG5vdCBkaWZmZXIgaW4gcHJvZHVjdGlvbi5cclxuICovXHJcblxyXG52YXIgaW52YXJpYW50ID0gZnVuY3Rpb24oY29uZGl0aW9uLCBmb3JtYXQsIGEsIGIsIGMsIGQsIGUsIGYpIHtcclxuICBpZiAoZmFsc2UpIHtcclxuICAgIGlmIChmb3JtYXQgPT09IHVuZGVmaW5lZCkge1xyXG4gICAgICB0aHJvdyBuZXcgRXJyb3IoJ2ludmFyaWFudCByZXF1aXJlcyBhbiBlcnJvciBtZXNzYWdlIGFyZ3VtZW50Jyk7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICBpZiAoIWNvbmRpdGlvbikge1xyXG4gICAgdmFyIGVycm9yO1xyXG4gICAgaWYgKGZvcm1hdCA9PT0gdW5kZWZpbmVkKSB7XHJcbiAgICAgIGVycm9yID0gbmV3IEVycm9yKFxyXG4gICAgICAgICdNaW5pZmllZCBleGNlcHRpb24gb2NjdXJyZWQ7IHVzZSB0aGUgbm9uLW1pbmlmaWVkIGRldiBlbnZpcm9ubWVudCAnICtcclxuICAgICAgICAnZm9yIHRoZSBmdWxsIGVycm9yIG1lc3NhZ2UgYW5kIGFkZGl0aW9uYWwgaGVscGZ1bCB3YXJuaW5ncy4nXHJcbiAgICAgICk7XHJcbiAgICB9IGVsc2Uge1xyXG4gICAgICB2YXIgYXJncyA9IFthLCBiLCBjLCBkLCBlLCBmXTtcclxuICAgICAgdmFyIGFyZ0luZGV4ID0gMDtcclxuICAgICAgZXJyb3IgPSBuZXcgRXJyb3IoXHJcbiAgICAgICAgJ0ludmFyaWFudCBWaW9sYXRpb246ICcgK1xyXG4gICAgICAgIGZvcm1hdC5yZXBsYWNlKC8lcy9nLCBmdW5jdGlvbigpIHsgcmV0dXJuIGFyZ3NbYXJnSW5kZXgrK107IH0pXHJcbiAgICAgICk7XHJcbiAgICB9XHJcblxyXG4gICAgZXJyb3IuZnJhbWVzVG9Qb3AgPSAxOyAvLyB3ZSBkb24ndCBjYXJlIGFib3V0IGludmFyaWFudCdzIG93biBmcmFtZVxyXG4gICAgdGhyb3cgZXJyb3I7XHJcbiAgfVxyXG59O1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBpbnZhcmlhbnQ7XHJcbiIsIi8qKlxyXG4gKiBDb3B5cmlnaHQgMjAxMy0yMDE0IEZhY2Vib29rLCBJbmMuXHJcbiAqXHJcbiAqIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5zZSwgVmVyc2lvbiAyLjAgKHRoZSBcIkxpY2Vuc2VcIik7XHJcbiAqIHlvdSBtYXkgbm90IHVzZSB0aGlzIGZpbGUgZXhjZXB0IGluIGNvbXBsaWFuY2Ugd2l0aCB0aGUgTGljZW5zZS5cclxuICogWW91IG1heSBvYnRhaW4gYSBjb3B5IG9mIHRoZSBMaWNlbnNlIGF0XHJcbiAqXHJcbiAqIGh0dHA6Ly93d3cuYXBhY2hlLm9yZy9saWNlbnNlcy9MSUNFTlNFLTIuMFxyXG4gKlxyXG4gKiBVbmxlc3MgcmVxdWlyZWQgYnkgYXBwbGljYWJsZSBsYXcgb3IgYWdyZWVkIHRvIGluIHdyaXRpbmcsIHNvZnR3YXJlXHJcbiAqIGRpc3RyaWJ1dGVkIHVuZGVyIHRoZSBMaWNlbnNlIGlzIGRpc3RyaWJ1dGVkIG9uIGFuIFwiQVMgSVNcIiBCQVNJUyxcclxuICogV0lUSE9VVCBXQVJSQU5USUVTIE9SIENPTkRJVElPTlMgT0YgQU5ZIEtJTkQsIGVpdGhlciBleHByZXNzIG9yIGltcGxpZWQuXHJcbiAqIFNlZSB0aGUgTGljZW5zZSBmb3IgdGhlIHNwZWNpZmljIGxhbmd1YWdlIGdvdmVybmluZyBwZXJtaXNzaW9ucyBhbmRcclxuICogbGltaXRhdGlvbnMgdW5kZXIgdGhlIExpY2Vuc2UuXHJcbiAqXHJcbiAqL1xyXG5cclxuXCJ1c2Ugc3RyaWN0XCI7XHJcblxyXG4vKipcclxuICogQ29uc3RydWN0cyBhbiBlbnVtZXJhdGlvbiB3aXRoIGtleXMgZXF1YWwgdG8gdGhlaXIgdmFsdWUuXHJcbiAqXHJcbiAqIEZvciBleGFtcGxlOlxyXG4gKlxyXG4gKiAgIHZhciBDT0xPUlMgPSBrZXlNaXJyb3Ioe2JsdWU6IG51bGwsIHJlZDogbnVsbH0pO1xyXG4gKiAgIHZhciBteUNvbG9yID0gQ09MT1JTLmJsdWU7XHJcbiAqICAgdmFyIGlzQ29sb3JWYWxpZCA9ICEhQ09MT1JTW215Q29sb3JdO1xyXG4gKlxyXG4gKiBUaGUgbGFzdCBsaW5lIGNvdWxkIG5vdCBiZSBwZXJmb3JtZWQgaWYgdGhlIHZhbHVlcyBvZiB0aGUgZ2VuZXJhdGVkIGVudW0gd2VyZVxyXG4gKiBub3QgZXF1YWwgdG8gdGhlaXIga2V5cy5cclxuICpcclxuICogICBJbnB1dDogIHtrZXkxOiB2YWwxLCBrZXkyOiB2YWwyfVxyXG4gKiAgIE91dHB1dDoge2tleTE6IGtleTEsIGtleTI6IGtleTJ9XHJcbiAqXHJcbiAqIEBwYXJhbSB7b2JqZWN0fSBvYmpcclxuICogQHJldHVybiB7b2JqZWN0fVxyXG4gKi9cclxudmFyIGtleU1pcnJvciA9IGZ1bmN0aW9uKG9iaikge1xyXG4gIHZhciByZXQgPSB7fTtcclxuICB2YXIga2V5O1xyXG4gIGlmICghKG9iaiBpbnN0YW5jZW9mIE9iamVjdCAmJiAhQXJyYXkuaXNBcnJheShvYmopKSkge1xyXG4gICAgdGhyb3cgbmV3IEVycm9yKCdrZXlNaXJyb3IoLi4uKTogQXJndW1lbnQgbXVzdCBiZSBhbiBvYmplY3QuJyk7XHJcbiAgfVxyXG4gIGZvciAoa2V5IGluIG9iaikge1xyXG4gICAgaWYgKCFvYmouaGFzT3duUHJvcGVydHkoa2V5KSkge1xyXG4gICAgICBjb250aW51ZTtcclxuICAgIH1cclxuICAgIHJldFtrZXldID0ga2V5O1xyXG4gIH1cclxuICByZXR1cm4gcmV0O1xyXG59O1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBrZXlNaXJyb3I7XHJcbiIsIid1c2Ugc3RyaWN0JztcclxuXHJcbmZ1bmN0aW9uIFRvT2JqZWN0KHZhbCkge1xyXG5cdGlmICh2YWwgPT0gbnVsbCkge1xyXG5cdFx0dGhyb3cgbmV3IFR5cGVFcnJvcignT2JqZWN0LmFzc2lnbiBjYW5ub3QgYmUgY2FsbGVkIHdpdGggbnVsbCBvciB1bmRlZmluZWQnKTtcclxuXHR9XHJcblxyXG5cdHJldHVybiBPYmplY3QodmFsKTtcclxufVxyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBPYmplY3QuYXNzaWduIHx8IGZ1bmN0aW9uICh0YXJnZXQsIHNvdXJjZSkge1xyXG5cdHZhciBwZW5kaW5nRXhjZXB0aW9uO1xyXG5cdHZhciBmcm9tO1xyXG5cdHZhciBrZXlzO1xyXG5cdHZhciB0byA9IFRvT2JqZWN0KHRhcmdldCk7XHJcblxyXG5cdGZvciAodmFyIHMgPSAxOyBzIDwgYXJndW1lbnRzLmxlbmd0aDsgcysrKSB7XHJcblx0XHRmcm9tID0gYXJndW1lbnRzW3NdO1xyXG5cdFx0a2V5cyA9IE9iamVjdC5rZXlzKE9iamVjdChmcm9tKSk7XHJcblxyXG5cdFx0Zm9yICh2YXIgaSA9IDA7IGkgPCBrZXlzLmxlbmd0aDsgaSsrKSB7XHJcblx0XHRcdHRyeSB7XHJcblx0XHRcdFx0dG9ba2V5c1tpXV0gPSBmcm9tW2tleXNbaV1dO1xyXG5cdFx0XHR9IGNhdGNoIChlcnIpIHtcclxuXHRcdFx0XHRpZiAocGVuZGluZ0V4Y2VwdGlvbiA9PT0gdW5kZWZpbmVkKSB7XHJcblx0XHRcdFx0XHRwZW5kaW5nRXhjZXB0aW9uID0gZXJyO1xyXG5cdFx0XHRcdH1cclxuXHRcdFx0fVxyXG5cdFx0fVxyXG5cdH1cclxuXHJcblx0aWYgKHBlbmRpbmdFeGNlcHRpb24pIHtcclxuXHRcdHRocm93IHBlbmRpbmdFeGNlcHRpb247XHJcblx0fVxyXG5cclxuXHRyZXR1cm4gdG87XHJcbn07XHJcbiIsIi8vIENvcHlyaWdodCBKb3llbnQsIEluYy4gYW5kIG90aGVyIE5vZGUgY29udHJpYnV0b3JzLlxyXG4vL1xyXG4vLyBQZXJtaXNzaW9uIGlzIGhlcmVieSBncmFudGVkLCBmcmVlIG9mIGNoYXJnZSwgdG8gYW55IHBlcnNvbiBvYnRhaW5pbmcgYVxyXG4vLyBjb3B5IG9mIHRoaXMgc29mdHdhcmUgYW5kIGFzc29jaWF0ZWQgZG9jdW1lbnRhdGlvbiBmaWxlcyAodGhlXHJcbi8vIFwiU29mdHdhcmVcIiksIHRvIGRlYWwgaW4gdGhlIFNvZnR3YXJlIHdpdGhvdXQgcmVzdHJpY3Rpb24sIGluY2x1ZGluZ1xyXG4vLyB3aXRob3V0IGxpbWl0YXRpb24gdGhlIHJpZ2h0cyB0byB1c2UsIGNvcHksIG1vZGlmeSwgbWVyZ2UsIHB1Ymxpc2gsXHJcbi8vIGRpc3RyaWJ1dGUsIHN1YmxpY2Vuc2UsIGFuZC9vciBzZWxsIGNvcGllcyBvZiB0aGUgU29mdHdhcmUsIGFuZCB0byBwZXJtaXRcclxuLy8gcGVyc29ucyB0byB3aG9tIHRoZSBTb2Z0d2FyZSBpcyBmdXJuaXNoZWQgdG8gZG8gc28sIHN1YmplY3QgdG8gdGhlXHJcbi8vIGZvbGxvd2luZyBjb25kaXRpb25zOlxyXG4vL1xyXG4vLyBUaGUgYWJvdmUgY29weXJpZ2h0IG5vdGljZSBhbmQgdGhpcyBwZXJtaXNzaW9uIG5vdGljZSBzaGFsbCBiZSBpbmNsdWRlZFxyXG4vLyBpbiBhbGwgY29waWVzIG9yIHN1YnN0YW50aWFsIHBvcnRpb25zIG9mIHRoZSBTb2Z0d2FyZS5cclxuLy9cclxuLy8gVEhFIFNPRlRXQVJFIElTIFBST1ZJREVEIFwiQVMgSVNcIiwgV0lUSE9VVCBXQVJSQU5UWSBPRiBBTlkgS0lORCwgRVhQUkVTU1xyXG4vLyBPUiBJTVBMSUVELCBJTkNMVURJTkcgQlVUIE5PVCBMSU1JVEVEIFRPIFRIRSBXQVJSQU5USUVTIE9GXHJcbi8vIE1FUkNIQU5UQUJJTElUWSwgRklUTkVTUyBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UgQU5EIE5PTklORlJJTkdFTUVOVC4gSU5cclxuLy8gTk8gRVZFTlQgU0hBTEwgVEhFIEFVVEhPUlMgT1IgQ09QWVJJR0hUIEhPTERFUlMgQkUgTElBQkxFIEZPUiBBTlkgQ0xBSU0sXHJcbi8vIERBTUFHRVMgT1IgT1RIRVIgTElBQklMSVRZLCBXSEVUSEVSIElOIEFOIEFDVElPTiBPRiBDT05UUkFDVCwgVE9SVCBPUlxyXG4vLyBPVEhFUldJU0UsIEFSSVNJTkcgRlJPTSwgT1VUIE9GIE9SIElOIENPTk5FQ1RJT04gV0lUSCBUSEUgU09GVFdBUkUgT1IgVEhFXHJcbi8vIFVTRSBPUiBPVEhFUiBERUFMSU5HUyBJTiBUSEUgU09GVFdBUkUuXHJcblxyXG5mdW5jdGlvbiBFdmVudEVtaXR0ZXIoKSB7XHJcbiAgdGhpcy5fZXZlbnRzID0gdGhpcy5fZXZlbnRzIHx8IHt9O1xyXG4gIHRoaXMuX21heExpc3RlbmVycyA9IHRoaXMuX21heExpc3RlbmVycyB8fCB1bmRlZmluZWQ7XHJcbn1cclxubW9kdWxlLmV4cG9ydHMgPSBFdmVudEVtaXR0ZXI7XHJcblxyXG4vLyBCYWNrd2FyZHMtY29tcGF0IHdpdGggbm9kZSAwLjEwLnhcclxuRXZlbnRFbWl0dGVyLkV2ZW50RW1pdHRlciA9IEV2ZW50RW1pdHRlcjtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuX2V2ZW50cyA9IHVuZGVmaW5lZDtcclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5fbWF4TGlzdGVuZXJzID0gdW5kZWZpbmVkO1xyXG5cclxuLy8gQnkgZGVmYXVsdCBFdmVudEVtaXR0ZXJzIHdpbGwgcHJpbnQgYSB3YXJuaW5nIGlmIG1vcmUgdGhhbiAxMCBsaXN0ZW5lcnMgYXJlXHJcbi8vIGFkZGVkIHRvIGl0LiBUaGlzIGlzIGEgdXNlZnVsIGRlZmF1bHQgd2hpY2ggaGVscHMgZmluZGluZyBtZW1vcnkgbGVha3MuXHJcbkV2ZW50RW1pdHRlci5kZWZhdWx0TWF4TGlzdGVuZXJzID0gMTA7XHJcblxyXG4vLyBPYnZpb3VzbHkgbm90IGFsbCBFbWl0dGVycyBzaG91bGQgYmUgbGltaXRlZCB0byAxMC4gVGhpcyBmdW5jdGlvbiBhbGxvd3NcclxuLy8gdGhhdCB0byBiZSBpbmNyZWFzZWQuIFNldCB0byB6ZXJvIGZvciB1bmxpbWl0ZWQuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuc2V0TWF4TGlzdGVuZXJzID0gZnVuY3Rpb24obikge1xyXG4gIGlmICghaXNOdW1iZXIobikgfHwgbiA8IDAgfHwgaXNOYU4obikpXHJcbiAgICB0aHJvdyBUeXBlRXJyb3IoJ24gbXVzdCBiZSBhIHBvc2l0aXZlIG51bWJlcicpO1xyXG4gIHRoaXMuX21heExpc3RlbmVycyA9IG47XHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLmVtaXQgPSBmdW5jdGlvbih0eXBlKSB7XHJcbiAgdmFyIGVyLCBoYW5kbGVyLCBsZW4sIGFyZ3MsIGksIGxpc3RlbmVycztcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHMpXHJcbiAgICB0aGlzLl9ldmVudHMgPSB7fTtcclxuXHJcbiAgLy8gSWYgdGhlcmUgaXMgbm8gJ2Vycm9yJyBldmVudCBsaXN0ZW5lciB0aGVuIHRocm93LlxyXG4gIGlmICh0eXBlID09PSAnZXJyb3InKSB7XHJcbiAgICBpZiAoIXRoaXMuX2V2ZW50cy5lcnJvciB8fFxyXG4gICAgICAgIChpc09iamVjdCh0aGlzLl9ldmVudHMuZXJyb3IpICYmICF0aGlzLl9ldmVudHMuZXJyb3IubGVuZ3RoKSkge1xyXG4gICAgICBlciA9IGFyZ3VtZW50c1sxXTtcclxuICAgICAgaWYgKGVyIGluc3RhbmNlb2YgRXJyb3IpIHtcclxuICAgICAgICB0aHJvdyBlcjsgLy8gVW5oYW5kbGVkICdlcnJvcicgZXZlbnRcclxuICAgICAgfVxyXG4gICAgICB0aHJvdyBUeXBlRXJyb3IoJ1VuY2F1Z2h0LCB1bnNwZWNpZmllZCBcImVycm9yXCIgZXZlbnQuJyk7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICBoYW5kbGVyID0gdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG5cclxuICBpZiAoaXNVbmRlZmluZWQoaGFuZGxlcikpXHJcbiAgICByZXR1cm4gZmFsc2U7XHJcblxyXG4gIGlmIChpc0Z1bmN0aW9uKGhhbmRsZXIpKSB7XHJcbiAgICBzd2l0Y2ggKGFyZ3VtZW50cy5sZW5ndGgpIHtcclxuICAgICAgLy8gZmFzdCBjYXNlc1xyXG4gICAgICBjYXNlIDE6XHJcbiAgICAgICAgaGFuZGxlci5jYWxsKHRoaXMpO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICBjYXNlIDI6XHJcbiAgICAgICAgaGFuZGxlci5jYWxsKHRoaXMsIGFyZ3VtZW50c1sxXSk7XHJcbiAgICAgICAgYnJlYWs7XHJcbiAgICAgIGNhc2UgMzpcclxuICAgICAgICBoYW5kbGVyLmNhbGwodGhpcywgYXJndW1lbnRzWzFdLCBhcmd1bWVudHNbMl0pO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICAvLyBzbG93ZXJcclxuICAgICAgZGVmYXVsdDpcclxuICAgICAgICBsZW4gPSBhcmd1bWVudHMubGVuZ3RoO1xyXG4gICAgICAgIGFyZ3MgPSBuZXcgQXJyYXkobGVuIC0gMSk7XHJcbiAgICAgICAgZm9yIChpID0gMTsgaSA8IGxlbjsgaSsrKVxyXG4gICAgICAgICAgYXJnc1tpIC0gMV0gPSBhcmd1bWVudHNbaV07XHJcbiAgICAgICAgaGFuZGxlci5hcHBseSh0aGlzLCBhcmdzKTtcclxuICAgIH1cclxuICB9IGVsc2UgaWYgKGlzT2JqZWN0KGhhbmRsZXIpKSB7XHJcbiAgICBsZW4gPSBhcmd1bWVudHMubGVuZ3RoO1xyXG4gICAgYXJncyA9IG5ldyBBcnJheShsZW4gLSAxKTtcclxuICAgIGZvciAoaSA9IDE7IGkgPCBsZW47IGkrKylcclxuICAgICAgYXJnc1tpIC0gMV0gPSBhcmd1bWVudHNbaV07XHJcblxyXG4gICAgbGlzdGVuZXJzID0gaGFuZGxlci5zbGljZSgpO1xyXG4gICAgbGVuID0gbGlzdGVuZXJzLmxlbmd0aDtcclxuICAgIGZvciAoaSA9IDA7IGkgPCBsZW47IGkrKylcclxuICAgICAgbGlzdGVuZXJzW2ldLmFwcGx5KHRoaXMsIGFyZ3MpO1xyXG4gIH1cclxuXHJcbiAgcmV0dXJuIHRydWU7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLmFkZExpc3RlbmVyID0gZnVuY3Rpb24odHlwZSwgbGlzdGVuZXIpIHtcclxuICB2YXIgbTtcclxuXHJcbiAgaWYgKCFpc0Z1bmN0aW9uKGxpc3RlbmVyKSlcclxuICAgIHRocm93IFR5cGVFcnJvcignbGlzdGVuZXIgbXVzdCBiZSBhIGZ1bmN0aW9uJyk7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzKVxyXG4gICAgdGhpcy5fZXZlbnRzID0ge307XHJcblxyXG4gIC8vIFRvIGF2b2lkIHJlY3Vyc2lvbiBpbiB0aGUgY2FzZSB0aGF0IHR5cGUgPT09IFwibmV3TGlzdGVuZXJcIiEgQmVmb3JlXHJcbiAgLy8gYWRkaW5nIGl0IHRvIHRoZSBsaXN0ZW5lcnMsIGZpcnN0IGVtaXQgXCJuZXdMaXN0ZW5lclwiLlxyXG4gIGlmICh0aGlzLl9ldmVudHMubmV3TGlzdGVuZXIpXHJcbiAgICB0aGlzLmVtaXQoJ25ld0xpc3RlbmVyJywgdHlwZSxcclxuICAgICAgICAgICAgICBpc0Z1bmN0aW9uKGxpc3RlbmVyLmxpc3RlbmVyKSA/XHJcbiAgICAgICAgICAgICAgbGlzdGVuZXIubGlzdGVuZXIgOiBsaXN0ZW5lcik7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzW3R5cGVdKVxyXG4gICAgLy8gT3B0aW1pemUgdGhlIGNhc2Ugb2Ygb25lIGxpc3RlbmVyLiBEb24ndCBuZWVkIHRoZSBleHRyYSBhcnJheSBvYmplY3QuXHJcbiAgICB0aGlzLl9ldmVudHNbdHlwZV0gPSBsaXN0ZW5lcjtcclxuICBlbHNlIGlmIChpc09iamVjdCh0aGlzLl9ldmVudHNbdHlwZV0pKVxyXG4gICAgLy8gSWYgd2UndmUgYWxyZWFkeSBnb3QgYW4gYXJyYXksIGp1c3QgYXBwZW5kLlxyXG4gICAgdGhpcy5fZXZlbnRzW3R5cGVdLnB1c2gobGlzdGVuZXIpO1xyXG4gIGVsc2VcclxuICAgIC8vIEFkZGluZyB0aGUgc2Vjb25kIGVsZW1lbnQsIG5lZWQgdG8gY2hhbmdlIHRvIGFycmF5LlxyXG4gICAgdGhpcy5fZXZlbnRzW3R5cGVdID0gW3RoaXMuX2V2ZW50c1t0eXBlXSwgbGlzdGVuZXJdO1xyXG5cclxuICAvLyBDaGVjayBmb3IgbGlzdGVuZXIgbGVha1xyXG4gIGlmIChpc09iamVjdCh0aGlzLl9ldmVudHNbdHlwZV0pICYmICF0aGlzLl9ldmVudHNbdHlwZV0ud2FybmVkKSB7XHJcbiAgICB2YXIgbTtcclxuICAgIGlmICghaXNVbmRlZmluZWQodGhpcy5fbWF4TGlzdGVuZXJzKSkge1xyXG4gICAgICBtID0gdGhpcy5fbWF4TGlzdGVuZXJzO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgbSA9IEV2ZW50RW1pdHRlci5kZWZhdWx0TWF4TGlzdGVuZXJzO1xyXG4gICAgfVxyXG5cclxuICAgIGlmIChtICYmIG0gPiAwICYmIHRoaXMuX2V2ZW50c1t0eXBlXS5sZW5ndGggPiBtKSB7XHJcbiAgICAgIHRoaXMuX2V2ZW50c1t0eXBlXS53YXJuZWQgPSB0cnVlO1xyXG4gICAgICBjb25zb2xlLmVycm9yKCcobm9kZSkgd2FybmluZzogcG9zc2libGUgRXZlbnRFbWl0dGVyIG1lbW9yeSAnICtcclxuICAgICAgICAgICAgICAgICAgICAnbGVhayBkZXRlY3RlZC4gJWQgbGlzdGVuZXJzIGFkZGVkLiAnICtcclxuICAgICAgICAgICAgICAgICAgICAnVXNlIGVtaXR0ZXIuc2V0TWF4TGlzdGVuZXJzKCkgdG8gaW5jcmVhc2UgbGltaXQuJyxcclxuICAgICAgICAgICAgICAgICAgICB0aGlzLl9ldmVudHNbdHlwZV0ubGVuZ3RoKTtcclxuICAgICAgaWYgKHR5cGVvZiBjb25zb2xlLnRyYWNlID09PSAnZnVuY3Rpb24nKSB7XHJcbiAgICAgICAgLy8gbm90IHN1cHBvcnRlZCBpbiBJRSAxMFxyXG4gICAgICAgIGNvbnNvbGUudHJhY2UoKTtcclxuICAgICAgfVxyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLm9uID0gRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5hZGRMaXN0ZW5lcjtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUub25jZSA9IGZ1bmN0aW9uKHR5cGUsIGxpc3RlbmVyKSB7XHJcbiAgaWYgKCFpc0Z1bmN0aW9uKGxpc3RlbmVyKSlcclxuICAgIHRocm93IFR5cGVFcnJvcignbGlzdGVuZXIgbXVzdCBiZSBhIGZ1bmN0aW9uJyk7XHJcblxyXG4gIHZhciBmaXJlZCA9IGZhbHNlO1xyXG5cclxuICBmdW5jdGlvbiBnKCkge1xyXG4gICAgdGhpcy5yZW1vdmVMaXN0ZW5lcih0eXBlLCBnKTtcclxuXHJcbiAgICBpZiAoIWZpcmVkKSB7XHJcbiAgICAgIGZpcmVkID0gdHJ1ZTtcclxuICAgICAgbGlzdGVuZXIuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcclxuICAgIH1cclxuICB9XHJcblxyXG4gIGcubGlzdGVuZXIgPSBsaXN0ZW5lcjtcclxuICB0aGlzLm9uKHR5cGUsIGcpO1xyXG5cclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbi8vIGVtaXRzIGEgJ3JlbW92ZUxpc3RlbmVyJyBldmVudCBpZmYgdGhlIGxpc3RlbmVyIHdhcyByZW1vdmVkXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUucmVtb3ZlTGlzdGVuZXIgPSBmdW5jdGlvbih0eXBlLCBsaXN0ZW5lcikge1xyXG4gIHZhciBsaXN0LCBwb3NpdGlvbiwgbGVuZ3RoLCBpO1xyXG5cclxuICBpZiAoIWlzRnVuY3Rpb24obGlzdGVuZXIpKVxyXG4gICAgdGhyb3cgVHlwZUVycm9yKCdsaXN0ZW5lciBtdXN0IGJlIGEgZnVuY3Rpb24nKTtcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHMgfHwgIXRoaXMuX2V2ZW50c1t0eXBlXSlcclxuICAgIHJldHVybiB0aGlzO1xyXG5cclxuICBsaXN0ID0gdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG4gIGxlbmd0aCA9IGxpc3QubGVuZ3RoO1xyXG4gIHBvc2l0aW9uID0gLTE7XHJcblxyXG4gIGlmIChsaXN0ID09PSBsaXN0ZW5lciB8fFxyXG4gICAgICAoaXNGdW5jdGlvbihsaXN0Lmxpc3RlbmVyKSAmJiBsaXN0Lmxpc3RlbmVyID09PSBsaXN0ZW5lcikpIHtcclxuICAgIGRlbGV0ZSB0aGlzLl9ldmVudHNbdHlwZV07XHJcbiAgICBpZiAodGhpcy5fZXZlbnRzLnJlbW92ZUxpc3RlbmVyKVxyXG4gICAgICB0aGlzLmVtaXQoJ3JlbW92ZUxpc3RlbmVyJywgdHlwZSwgbGlzdGVuZXIpO1xyXG5cclxuICB9IGVsc2UgaWYgKGlzT2JqZWN0KGxpc3QpKSB7XHJcbiAgICBmb3IgKGkgPSBsZW5ndGg7IGktLSA+IDA7KSB7XHJcbiAgICAgIGlmIChsaXN0W2ldID09PSBsaXN0ZW5lciB8fFxyXG4gICAgICAgICAgKGxpc3RbaV0ubGlzdGVuZXIgJiYgbGlzdFtpXS5saXN0ZW5lciA9PT0gbGlzdGVuZXIpKSB7XHJcbiAgICAgICAgcG9zaXRpb24gPSBpO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICB9XHJcbiAgICB9XHJcblxyXG4gICAgaWYgKHBvc2l0aW9uIDwgMClcclxuICAgICAgcmV0dXJuIHRoaXM7XHJcblxyXG4gICAgaWYgKGxpc3QubGVuZ3RoID09PSAxKSB7XHJcbiAgICAgIGxpc3QubGVuZ3RoID0gMDtcclxuICAgICAgZGVsZXRlIHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuICAgIH0gZWxzZSB7XHJcbiAgICAgIGxpc3Quc3BsaWNlKHBvc2l0aW9uLCAxKTtcclxuICAgIH1cclxuXHJcbiAgICBpZiAodGhpcy5fZXZlbnRzLnJlbW92ZUxpc3RlbmVyKVxyXG4gICAgICB0aGlzLmVtaXQoJ3JlbW92ZUxpc3RlbmVyJywgdHlwZSwgbGlzdGVuZXIpO1xyXG4gIH1cclxuXHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLnJlbW92ZUFsbExpc3RlbmVycyA9IGZ1bmN0aW9uKHR5cGUpIHtcclxuICB2YXIga2V5LCBsaXN0ZW5lcnM7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzKVxyXG4gICAgcmV0dXJuIHRoaXM7XHJcblxyXG4gIC8vIG5vdCBsaXN0ZW5pbmcgZm9yIHJlbW92ZUxpc3RlbmVyLCBubyBuZWVkIHRvIGVtaXRcclxuICBpZiAoIXRoaXMuX2V2ZW50cy5yZW1vdmVMaXN0ZW5lcikge1xyXG4gICAgaWYgKGFyZ3VtZW50cy5sZW5ndGggPT09IDApXHJcbiAgICAgIHRoaXMuX2V2ZW50cyA9IHt9O1xyXG4gICAgZWxzZSBpZiAodGhpcy5fZXZlbnRzW3R5cGVdKVxyXG4gICAgICBkZWxldGUgdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG4gICAgcmV0dXJuIHRoaXM7XHJcbiAgfVxyXG5cclxuICAvLyBlbWl0IHJlbW92ZUxpc3RlbmVyIGZvciBhbGwgbGlzdGVuZXJzIG9uIGFsbCBldmVudHNcclxuICBpZiAoYXJndW1lbnRzLmxlbmd0aCA9PT0gMCkge1xyXG4gICAgZm9yIChrZXkgaW4gdGhpcy5fZXZlbnRzKSB7XHJcbiAgICAgIGlmIChrZXkgPT09ICdyZW1vdmVMaXN0ZW5lcicpIGNvbnRpbnVlO1xyXG4gICAgICB0aGlzLnJlbW92ZUFsbExpc3RlbmVycyhrZXkpO1xyXG4gICAgfVxyXG4gICAgdGhpcy5yZW1vdmVBbGxMaXN0ZW5lcnMoJ3JlbW92ZUxpc3RlbmVyJyk7XHJcbiAgICB0aGlzLl9ldmVudHMgPSB7fTtcclxuICAgIHJldHVybiB0aGlzO1xyXG4gIH1cclxuXHJcbiAgbGlzdGVuZXJzID0gdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG5cclxuICBpZiAoaXNGdW5jdGlvbihsaXN0ZW5lcnMpKSB7XHJcbiAgICB0aGlzLnJlbW92ZUxpc3RlbmVyKHR5cGUsIGxpc3RlbmVycyk7XHJcbiAgfSBlbHNlIHtcclxuICAgIC8vIExJRk8gb3JkZXJcclxuICAgIHdoaWxlIChsaXN0ZW5lcnMubGVuZ3RoKVxyXG4gICAgICB0aGlzLnJlbW92ZUxpc3RlbmVyKHR5cGUsIGxpc3RlbmVyc1tsaXN0ZW5lcnMubGVuZ3RoIC0gMV0pO1xyXG4gIH1cclxuICBkZWxldGUgdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG5cclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUubGlzdGVuZXJzID0gZnVuY3Rpb24odHlwZSkge1xyXG4gIHZhciByZXQ7XHJcbiAgaWYgKCF0aGlzLl9ldmVudHMgfHwgIXRoaXMuX2V2ZW50c1t0eXBlXSlcclxuICAgIHJldCA9IFtdO1xyXG4gIGVsc2UgaWYgKGlzRnVuY3Rpb24odGhpcy5fZXZlbnRzW3R5cGVdKSlcclxuICAgIHJldCA9IFt0aGlzLl9ldmVudHNbdHlwZV1dO1xyXG4gIGVsc2VcclxuICAgIHJldCA9IHRoaXMuX2V2ZW50c1t0eXBlXS5zbGljZSgpO1xyXG4gIHJldHVybiByZXQ7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIubGlzdGVuZXJDb3VudCA9IGZ1bmN0aW9uKGVtaXR0ZXIsIHR5cGUpIHtcclxuICB2YXIgcmV0O1xyXG4gIGlmICghZW1pdHRlci5fZXZlbnRzIHx8ICFlbWl0dGVyLl9ldmVudHNbdHlwZV0pXHJcbiAgICByZXQgPSAwO1xyXG4gIGVsc2UgaWYgKGlzRnVuY3Rpb24oZW1pdHRlci5fZXZlbnRzW3R5cGVdKSlcclxuICAgIHJldCA9IDE7XHJcbiAgZWxzZVxyXG4gICAgcmV0ID0gZW1pdHRlci5fZXZlbnRzW3R5cGVdLmxlbmd0aDtcclxuICByZXR1cm4gcmV0O1xyXG59O1xyXG5cclxuZnVuY3Rpb24gaXNGdW5jdGlvbihhcmcpIHtcclxuICByZXR1cm4gdHlwZW9mIGFyZyA9PT0gJ2Z1bmN0aW9uJztcclxufVxyXG5cclxuZnVuY3Rpb24gaXNOdW1iZXIoYXJnKSB7XHJcbiAgcmV0dXJuIHR5cGVvZiBhcmcgPT09ICdudW1iZXInO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpc09iamVjdChhcmcpIHtcclxuICByZXR1cm4gdHlwZW9mIGFyZyA9PT0gJ29iamVjdCcgJiYgYXJnICE9PSBudWxsO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpc1VuZGVmaW5lZChhcmcpIHtcclxuICByZXR1cm4gYXJnID09PSB2b2lkIDA7XHJcbn1cclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcblxyXG4vKioqKioqKioqKiogIFrDgVZJU0xPU1RJICAqKioqKioqKioqKi9cclxudmFyIFByb2ZpbGVQaG90byA9IHJlcXVpcmUoJy4uL2NvbXBvbmVudHMvcHJvZmlsZScpLlByb2ZpbGVQaG90bztcclxudmFyIE1lc3NhZ2VBY3Rpb25zID0gcmVxdWlyZSgnLi4vZmx1eC9hY3Rpb25zL2NoYXQvTWVzc2FnZUFjdGlvbkNyZWF0b3JzJyk7XHJcbnZhciBNZXNzYWdlU3RvcmUgPSByZXF1aXJlKCcuLi9mbHV4L3N0b3Jlcy9jaGF0L01lc3NhZ2VTdG9yZScpO1xyXG52YXIgVGltZXJGYWN0b3J5ID0gcmVxdWlyZSgnLi4vY29tcG9uZW50cy90aW1lcicpOy8qIGplIHYgY2FjaGksIG5lYnVkZSBzZSB2eXR2w6HFmWV0IHbDrWNla3LDoXQgKi9cclxuXHJcbi8qKioqKioqKioqKiAgTkFTVEFWRU7DjSAgKioqKioqKioqKiovXHJcblxyXG4vKiogT2RrYXp5IGtlIGtvbXVuaWthY2kgKi9cclxudmFyIHJlYWN0U2VuZE1lc3NhZ2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0U2VuZE1lc3NhZ2VMaW5rJyk7XHJcbnZhciByZWFjdFJlZnJlc2hNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRSZWZyZXNoTWVzc2FnZXNMaW5rJyk7XHJcbnZhciByZWFjdExvYWRNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRMb2FkTWVzc2FnZXNMaW5rJyk7XHJcbnZhciByZWFjdEdldE9sZGVyTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0R2V0T2xkZXJNZXNzYWdlc0xpbmsnKTtcclxuLyogayBwb3Nsw6Fuw60genByw6F2eSovXHJcbnZhciByZWFjdFNlbmRNZXNzYWdlTGluayA9IHJlYWN0U2VuZE1lc3NhZ2UuaHJlZjtcclxuLyogayBwcmF2aWRlbG7DqW11IGRvdGF6dSBuYSB6cHLDoXZ5ICovXHJcbnZhciByZWFjdFJlZnJlc2hNZXNzYWdlc0xpbmsgPSByZWFjdFJlZnJlc2hNZXNzYWdlcy5ocmVmO1xyXG4vKiBrIGRvdGF6dSBuYSBuYcSNdGVuw60genByw6F2LCBrZHnFviBuZW3DoW0gemF0w61tIMW+w6FkbsOpICh0eXBpY2t5IHBvc2xlZG7DrSB6cHLDoXZ5IG1lemkgdcW+aXZhdGVsaSkgKi9cclxudmFyIHJlYWN0TG9hZE1lc3NhZ2VzTGluayA9IHJlYWN0TG9hZE1lc3NhZ2VzLmhyZWY7XHJcbi8qIGsgZG90YXp1IG5hIHN0YXLFocOtIHpwcsOhdnkgKi9cclxudmFyIHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmsgPSByZWFjdEdldE9sZGVyTWVzc2FnZXMuaHJlZjtcclxuLyoqIHByZWZpeCBwxZllZCBwYXJhbWV0cnkgZG8gdXJsICovXHJcbnZhciBwYXJhbWV0ZXJzUHJlZml4ID0gcmVhY3RTZW5kTWVzc2FnZS5kYXRhc2V0LnBhcnByZWZpeDtcclxuLyoqIG9idnlrbMO9IHBvxI1ldCBwxZnDrWNob3rDrWNoIHpwcsOhdiB2IG9kcG92xJtkaSB1IHByYXZpZGVsbsOpaG8gYSBpbmljacOhbG7DrWhvIHBvxb5hZGF2a3UgKGFuZWIga29saWsgenByw6F2IG1pIHDFmWlqZGUsIGtkecW+IGppY2ggamUgbmEgc2VydmVydSBqZcWhdMSbIGRvc3QpICovXHJcbnZhciB1c3VhbE9sZGVyTWVzc2FnZXNDb3VudCA9IHJlYWN0R2V0T2xkZXJNZXNzYWdlcy5kYXRhc2V0Lm1heG1lc3NhZ2VzO1xyXG52YXIgdXN1YWxMb2FkTWVzc2FnZXNDb3VudCA9IHJlYWN0TG9hZE1lc3NhZ2VzLmRhdGFzZXQubWF4bWVzc2FnZXM7XHJcbi8qIMSNYXNvdmHEjSBwcm8gcHJhdmlkZWxuw6kgcG/FvmFkYXZreSBuYSBzZXJ2ZXIgKi9cclxudmFyIFRpbWVyID0gVGltZXJGYWN0b3J5Lm5ld0luc3RhbmNlKCk7XHJcblxyXG4vKioqKioqKioqKiogIERFRklOSUNFICAqKioqKioqKioqKi9cclxuLyoqIMSMw6FzdCBva25hLCBrdGVyw6EgbcOhIHN2aXNsw70gcG9zdXZuw61rIC0gb2JzYWh1amUgenByw6F2eSwgdGxhxI3DrXRrbyBwcm8gZG9uYcSNw610w6Fuw60uLi4gKi9cclxudmFyIE1lc3NhZ2VzV2luZG93ID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIk1lc3NhZ2VzV2luZG93XCIsXHJcbiAgZ2V0SW5pdGlhbFN0YXRlOiBmdW5jdGlvbigpIHtcclxuICAgIHJldHVybiB7bWVzc2FnZXM6IFtdLCBpbmZvTWVzc2FnZXM6IFtdLCB0aGVyZUlzTW9yZTogdHJ1ZSwgaHJlZjogJycgfTtcclxuICB9LFxyXG4gIGNvbXBvbmVudERpZE1vdW50OiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBjb21wb25lbnQgPSB0aGlzO1xyXG4gICAgTWVzc2FnZVN0b3JlLmFkZENoYW5nZUxpc3RlbmVyKGZ1bmN0aW9uKCl7XHJcbiAgICAgIGNvbXBvbmVudC5zZXRTdGF0ZShNZXNzYWdlU3RvcmUuZ2V0U3RhdGUoKSk7XHJcbiAgICB9KTtcclxuICAgIE1lc3NhZ2VBY3Rpb25zLmNyZWF0ZUdldEluaXRpYWxNZXNzYWdlcyhyZWFjdExvYWRNZXNzYWdlc0xpbmssIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIHBhcmFtZXRlcnNQcmVmaXgsIHVzdWFsTG9hZE1lc3NhZ2VzQ291bnQpO1xyXG4gIH0sXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBtZXNzYWdlcyA9IHRoaXMuc3RhdGUubWVzc2FnZXM7XHJcbiAgICB2YXIgaW5mb01lc3NhZ2VzID0gdGhpcy5zdGF0ZS5pbmZvTWVzc2FnZXM7XHJcbiAgICB2YXIgb2xkZXN0SWQgPSB0aGlzLmdldE9sZGVzdElkKG1lc3NhZ2VzKTtcclxuICAgIHZhciB1c2VyQ29kZWRJZCA9IHRoaXMucHJvcHMudXNlckNvZGVkSWQ7XHJcbiAgICAvKiBzZXN0YXZlbsOtIG9ka2F6dSBwcm8gdGxhxI3DrXRrbyAqL1xyXG4gICAgdmFyIG1vcmVCdXR0b25MaW5rID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzTGluayArICcmJyArIHBhcmFtZXRlcnNQcmVmaXggKyAnbGFzdElkPScgKyBvbGRlc3RJZCArICcmJyArIHBhcmFtZXRlcnNQcmVmaXggKyAnd2l0aFVzZXJJZD0nICsgdGhpcy5wcm9wcy51c2VyQ29kZWRJZDtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlc1dpbmRvd1wifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChMb2FkTW9yZUJ1dHRvbiwge2xvYWRIcmVmOiBtb3JlQnV0dG9uTGluaywgb2xkZXN0SWQ6IG9sZGVzdElkLCB0aGVyZUlzTW9yZTogdGhpcy5zdGF0ZS50aGVyZUlzTW9yZSwgdXNlckNvZGVkSWQ6IHVzZXJDb2RlZElkfSksIFxyXG4gICAgICAgIG1lc3NhZ2VzLm1hcChmdW5jdGlvbihtZXNzYWdlKXtcclxuICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoTWVzc2FnZSwge2tleTogbWVzc2FnZS5pZCwgbWVzc2FnZURhdGE6IG1lc3NhZ2UsIHVzZXJIcmVmOiBtZXNzYWdlLnByb2ZpbGVIcmVmLCBwcm9maWxlUGhvdG9Vcmw6IG1lc3NhZ2UucHJvZmlsZVBob3RvVXJsfSk7XHJcbiAgICAgICAgfSksIFxyXG4gICAgICAgIFxyXG4gICAgICAgIGluZm9NZXNzYWdlcy5tYXAoZnVuY3Rpb24obWVzc2FnZSwgaSl7XHJcbiAgICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoSW5mb01lc3NhZ2UsIHtrZXk6IHVzZXJDb2RlZElkICsgJ2luZm8nICsgaSwgbWVzc2FnZURhdGE6IG1lc3NhZ2V9KTtcclxuICAgICAgICAgIH0pXHJcbiAgICAgICAgXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBnZXRPbGRlc3RJZDogZnVuY3Rpb24obWVzc2FnZXMpe1xyXG4gICAgcmV0dXJuIChtZXNzYWdlc1swXSkgPyBtZXNzYWdlc1swXS5pZCA6IDkwMDcxOTkyNTQ3NDA5OTE7IC8qbmFzdGF2ZW7DrSBob2Rub3R5IG5lYm8gbWF4aW3DoWxuw60gaG9kbm90eSwga2R5xb4gbmVuw60qL1xyXG4gIH1cclxufSk7XHJcblxyXG52YXIgSW5mb01lc3NhZ2UgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiSW5mb01lc3NhZ2VcIixcclxuICByZW5kZXI6IGZ1bmN0aW9uKCl7XHJcbiAgICAgIHJldHVybihSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcImluZm8tbWVzc2FnZVwifSwgdGhpcy5wcm9wcy5tZXNzYWdlRGF0YS50ZXh0KSk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBKZWRuYSB6cHLDoXZhLiAqL1xyXG52YXIgTWVzc2FnZSA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBtZXNzYWdlID0gdGhpcy5wcm9wcy5tZXNzYWdlRGF0YTtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlXCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiB0aGlzLnByb3BzLnVzZXJIcmVmLCB1c2VyTmFtZTogbWVzc2FnZS5uYW1lLCBwcm9maWxlUGhvdG9Vcmw6IHRoaXMucHJvcHMucHJvZmlsZVBob3RvVXJsfSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlQXJyb3dcIn0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwicFwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VUZXh0XCJ9LCBcclxuICAgICAgICAgIG1lc3NhZ2UudGV4dCwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VEYXRldGltZVwifSwgbWVzc2FnZS5zZW5kZWREYXRlKVxyXG4gICAgICAgICksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJjbGVhclwifSlcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqIERvbmHEjcOtdGFjw60gdGxhxI3DrXRrbyAqL1xyXG52YXIgTG9hZE1vcmVCdXR0b24gPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTG9hZE1vcmVCdXR0b25cIixcclxuICByZW5kZXI6IGZ1bmN0aW9uKCkge1xyXG4gICAgaWYoIXRoaXMucHJvcHMudGhlcmVJc01vcmUpeyByZXR1cm4gbnVsbDt9XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcImxvYWRNb3JlQnV0dG9uIGJ0bi1tYWluIGxvYWRpbmdidXR0b24gdWktYnRuXCIsIG9uQ2xpY2s6IHRoaXMuaGFuZGxlQ2xpY2t9LCBcbiAgICAgICAgXCJOYcSNw61zdCBkYWzFocOtIHpwcsOhdnlcIlxuICAgICAgKVxyXG4gICAgKTtcclxuICB9LFxyXG4gIGhhbmRsZUNsaWNrOiBmdW5jdGlvbigpe1xyXG4gICAgTWVzc2FnZUFjdGlvbnMuY3JlYXRlR2V0T2xkZXJNZXNzYWdlcyhyZWFjdEdldE9sZGVyTWVzc2FnZXNMaW5rLCB0aGlzLnByb3BzLnVzZXJDb2RlZElkLCB0aGlzLnByb3BzLm9sZGVzdElkLCBwYXJhbWV0ZXJzUHJlZml4LCB1c3VhbE9sZGVyTWVzc2FnZXNDb3VudCk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBGb3JtdWzDocWZIHBybyBvZGVzw61sw6Fuw60genByw6F2ICovXHJcbnZhciBOZXdNZXNzYWdlRm9ybSA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJOZXdNZXNzYWdlRm9ybVwiLFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbG9nZ2VkVXNlciA9IHRoaXMucHJvcHMubG9nZ2VkVXNlcjtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJuZXdNZXNzYWdlXCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiBsb2dnZWRVc2VyLmhyZWYsIHVzZXJOYW1lOiBsb2dnZWRVc2VyLm5hbWUsIHByb2ZpbGVQaG90b1VybDogbG9nZ2VkVXNlci5wcm9maWxlUGhvdG9Vcmx9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VBcnJvd1wifSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJmb3JtXCIsIHtvblN1Ym1pdDogdGhpcy5vblN1Ym1pdH0sIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInRleHRcIiwgY2xhc3NOYW1lOiBcIm1lc3NhZ2VJbnB1dFwifSksIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInN1Ym1pdFwiLCBjbGFzc05hbWU6IFwiYnRuLW1haW4gbWVkaXVtIGJ1dHRvblwiLCB2YWx1ZTogXCJPZGVzbGF0XCJ9KVxyXG4gICAgICAgIClcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9LFxyXG4gIG9uU3VibWl0OiBmdW5jdGlvbihlKXsvKiBWZXptZSB6cHLDoXZ1IHplIHN1Ym1pdHUgYSBwb8WhbGUgamkuIFRha8OpIHNtYcW+ZSB6cHLDoXZ1IG5hcHNhbm91IHYgaW5wdXR1LiAqL1xyXG4gICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xyXG4gICAgdmFyIGlucHV0ID0gZS50YXJnZXQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnbWVzc2FnZUlucHV0JylbMF07XHJcbiAgICB2YXIgbWVzc2FnZSA9IGlucHV0LnZhbHVlO1xyXG4gICAgaWYobWVzc2FnZSA9PSB1bmRlZmluZWQgfHwgbWVzc2FnZS50cmltKCkgPT0gJycpIHJldHVybjtcclxuICAgIGlucHV0LnZhbHVlID0gJyc7XHJcbiAgICBNZXNzYWdlQWN0aW9ucy5jcmVhdGVTZW5kTWVzc2FnZShyZWFjdFNlbmRNZXNzYWdlTGluaywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgbWVzc2FnZSk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKlxyXG4gKiBpbmljaWFsaXp1amUgxI1hc292YcSNIHByYXZpZGVsbsSbIHNlIGRvdGF6dWrDrWPDrSBuYSBub3bDqSB6cHLDoXZ5IHYgesOhdmlzbG9zdGkgbmEgdG9tLCBqYWsgc2UgbcSbbsOtIGRhdGEgdiBNZXNzYWdlU3RvcmVcclxuICogQHBhcmFtIHtzdHJpbmd9IHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZSwgc2Uga3RlcsO9bSBzaSBww63FoXVcclxuICovXHJcbnZhciBpbml0aWFsaXplQ2hhdFRpbWVyID0gZnVuY3Rpb24odXNlckNvZGVkSWQpe1xyXG4gIE1lc3NhZ2VTdG9yZS5hZGRDaGFuZ2VMaXN0ZW5lcihmdW5jdGlvbigpe1xyXG4gICAgdmFyIHN0YXRlID0gTWVzc2FnZVN0b3JlLmdldFN0YXRlKCk7XHJcbiAgICBpZihzdGF0ZS5kYXRhVmVyc2lvbiA9PSAxKXsvKiBkYXRhIHNlIHBvcHJ2w6kgem3Em25pbGEgKi9cclxuICAgICAgVGltZXIubWF4aW11bUludGVydmFsID0gNjAwMDA7XHJcbiAgICAgIFRpbWVyLmluaXRpYWxJbnRlcnZhbCA9IDMwMDA7XHJcbiAgICAgIFRpbWVyLmludGVydmFsSW5jcmFzZSA9IDIwMDA7XHJcbiAgICAgIFRpbWVyLmxhc3RJZCA9IChzdGF0ZS5tZXNzYWdlcy5sZW5ndGggPiAwKSA/IFRpbWVyLmxhc3RJZCA9IHN0YXRlLm1lc3NhZ2VzW3N0YXRlLm1lc3NhZ2VzLmxlbmd0aCAtIDFdLmlkIDogMDtcclxuICAgICAgVGltZXIudGljayA9IGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgTWVzc2FnZUFjdGlvbnMuY3JlYXRlUmVmcmVzaE1lc3NhZ2VzKHJlYWN0UmVmcmVzaE1lc3NhZ2VzTGluaywgdXNlckNvZGVkSWQsIFRpbWVyLmxhc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCk7XHJcbiAgICAgIH07XHJcbiAgICAgIFRpbWVyLnN0YXJ0KCk7XHJcbiAgICB9ZWxzZXsvKiBrZHnFviBzZSBkYXRhIG5lem3Em25pbGEgcG9wcnbDqSwgYWxlIHVyxI1pdMSbIHNlIHptxJtuaWxhICovXHJcbiAgICAgIFRpbWVyLmxhc3RJZCA9IChzdGF0ZS5tZXNzYWdlcy5sZW5ndGggPiAwKSA/IFRpbWVyLmxhc3RJZCA9IHN0YXRlLm1lc3NhZ2VzW3N0YXRlLm1lc3NhZ2VzLmxlbmd0aCAtIDFdLmlkIDogMDtcclxuICAgICAgVGltZXIucmVzZXRUaW1lKCk7XHJcbiAgICB9XHJcbiAgfSk7XHJcblxyXG59O1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSB7XHJcbiAgLyoqIE9rbm8gY2Vsw6lobyBjaGF0dSBzIGplZG7DrW0gdcW+aXZhdGVsZW0gKi9cclxuICBDaGF0V2luZG93OiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiQ2hhdFdpbmRvd1wiLFxyXG4gICAgY29tcG9uZW50RGlkTW91bnQ6IGZ1bmN0aW9uKCkge1xyXG4gICAgICBpbml0aWFsaXplQ2hhdFRpbWVyKHRoaXMucHJvcHMudXNlckNvZGVkSWQpO1xyXG4gICAgICBNZXNzYWdlQWN0aW9ucy5yZWxvYWRXaW5kb3dVbmxvYWQoKTtcclxuICAgIH0sXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2hhdFdpbmRvd1wifSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2VzV2luZG93LCB7dXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE5ld01lc3NhZ2VGb3JtLCB7bG9nZ2VkVXNlcjogdGhpcy5wcm9wcy5sb2dnZWRVc2VyLCB1c2VyQ29kZWRJZDogdGhpcy5wcm9wcy51c2VyQ29kZWRJZH0pXHJcbiAgICAgICAgKVxyXG4gICAgICApXHJcbiAgICB9XHJcbiAgfSlcclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG5cclxuICAvKiogS29tcG9uZW50YSBuYSBwcm9maWxvdm91IGZvdGt1ICovXHJcbiAgUHJvZmlsZVBob3RvOiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiUHJvZmlsZVBob3RvXCIsXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiYVwiLCB7Y2xhc3NOYW1lOiBcImdlbmVyYXRlZFByb2ZpbGVcIiwgaHJlZjogdGhpcy5wcm9wcy5wcm9maWxlTGluaywgdGl0bGU6IHRoaXMucHJvcHMudXNlck5hbWV9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiwge3NyYzogdGhpcy5wcm9wcy5wcm9maWxlUGhvdG9Vcmx9KVxyXG4gICAgICAgIClcclxuICAgICAgKTtcclxuICAgIH1cclxuICB9KVxyXG5cclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqIFTFmcOtZGEgemFqacWhxaV1asOtY8OtIHByYXZpZGVsbsOpIHRpa3lcclxuICovXHJcblxyXG4vKiBnbG9iYWwgUmVhY3QgKi8vKiBhYnkgTmV0YmVhbnMgbmV2eWhhem92YWwgY2h5Ynkga3bFr2xpIG5lZGVrbGFyb3ZhbsOpIHByb23Em25uw6kgKi9cclxuLyoqL1xyXG4vKiBUxZnDrWRhIHphamnFocWldWrDrWPDrSBwcmF2aWRlbG7DqSB0aWt5LCBrdGVyw6kgc2UgbW9ob3UgcyBrYcW+ZMO9bSB0aWtudXTDrW0gcHJvZGx1xb5vdmF0ICovXHJcbmZ1bmN0aW9uIFRpbWVyKCkge1xyXG4gIC8qXHJcbiAgICAgICEhISBORU3EmsWHVEUgVFlUTyBQQVJBTUVUUlkgUMWYw41NTyBWIFRPTVRPIFNPVUJPUlUsIFpNxJrFh1RFIEpFIFUgVkHFoMONIElOU1RBTkNFIFRJTUVSVSAhISFcclxuICAqL1xyXG4gIHRoaXMuY3VycmVudEludGVydmFsID0gMTAwMDsgLyogYWt0dcOhbG7DrSDEjWVrw6Fuw60gbWV6aSB0aWt5ICovXHJcbiAgdGhpcy5pbml0aWFsSW50ZXJ2YWwgPSAxMDAwOyAvKiBwb8SNw6F0ZcSNbsOtIGludGVydmFsICovXHJcbiAgdGhpcy5pbnRlcnZhbEluY3Jhc2UgPSAwOy8qIHp2w73FoWVuw60gaW50ZXJ2YWx1IHBvIGthxb5kw6ltIHRpa3UgKi9cclxuICB0aGlzLm1heGltdW1JbnRlcnZhbCA9IDIwMDAwOy8qIG1heGltw6FsbsOtIGludGVydmFsICovXHJcbiAgdGhpcy5ydW5uaW5nID0gZmFsc2U7IC8qIGluZGlrw6F0b3IsIHpkYSB0aW1lciBixJvFvsOtICovXHJcbiAgdGhpcy50aWNrID0gZnVuY3Rpb24oKXt9Oy8qIGZ1bmtjZSwgY28gc2Ugdm9sw6EgcMWZaSBrYcW+ZMOpbSB0aWt1ICovXHJcbiAgdGhpcy5zdGFydCA9IGZ1bmN0aW9uKCl7LyogZnVua2NlLCBrdGVyw6Egc3B1c3TDrSDEjWFzb3ZhxI0gKi9cclxuICAgIGlmKCF0aGlzLnJ1bm5pbmcpe1xyXG4gICAgICB0aGlzLnJ1bm5pbmcgPSB0cnVlO1xyXG4gICAgICB0aGlzLnJlc2V0VGltZSgpO1xyXG4gICAgICB0aGlzLnJlY3Vyc2l2ZSgpO1xyXG4gICAgfVxyXG4gIH07XHJcbiAgdGhpcy5zdG9wID0gZnVuY3Rpb24oKXsvKiBmdW5rY2UsIGt0ZXLDoSB0aW1lciB6YXN0YXbDrSovXHJcbiAgICB0aGlzLnJ1bm5pbmcgPSBmYWxzZTtcclxuICB9O1xyXG4gIHRoaXMucmVzZXRUaW1lID0gZnVuY3Rpb24oKXsvKiBmdW5rY2UsIGt0ZXJvdSB2eXJlc2V0dWppIMSNZWvDoW7DrSBuYSBwb8SNw6F0ZcSNbsOtIGhvZG5vdHUgKi9cclxuICAgIHRoaXMuY3VycmVudEludGVydmFsID0gdGhpcy5pbml0aWFsSW50ZXJ2YWw7XHJcbiAgfTtcclxuICB0aGlzLnJlY3Vyc2l2ZSA9IGZ1bmN0aW9uKCl7LyogbmVwxZlla3LDvXZhdCwgZnVua2NlLCBrdGVyw6EgZMSbbMOhIHNtecSNa3UgKi9cclxuICAgIGlmKHRoaXMucnVubmluZyl7XHJcbiAgICAgIHZhciB0aW1lciA9IHRoaXM7XHJcbiAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKXtcclxuICAgICAgICB0aW1lci50aWNrKCk7XHJcbiAgICAgICAgdGltZXIuY3VycmVudEludGVydmFsID0gTWF0aC5taW4odGltZXIuY3VycmVudEludGVydmFsICsgdGltZXIuaW50ZXJ2YWxJbmNyYXNlLCB0aW1lci5tYXhpbXVtSW50ZXJ2YWwpO1xyXG4gICAgICAgIHRpbWVyLnJlY3Vyc2l2ZSgpO1xyXG4gICAgICB9LCB0aW1lci5jdXJyZW50SW50ZXJ2YWwpO1xyXG4gICAgfVxyXG4gIH07XHJcblxyXG59XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuICBuZXdJbnN0YW5jZTogZnVuY3Rpb24oKXtcclxuICAgIHJldHVybiBuZXcgVGltZXIoKTtcclxuICB9XHJcbn1cclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqXHJcbiAqIFRlbnRvIHNvdWJvciB6YXN0xZllxaF1amUgZmx1eCBha2NlIHNvdXZpc2Vqw61jw60gc2UgesOtc2vDoXbDoW7DrW0genByw6F2LiBUYWvDqSB6cHJvc3TFmWVka292w6F2w6Ega29tdW5pa2FjaSBzZSBzZXJ2ZXJlbS5cclxuICovXHJcblxyXG4gdmFyIGRpc3BhdGNoZXIgPSByZXF1aXJlKCcuLi8uLi9kaXNwYXRjaGVyL2RhdGVub2RlRGlzcGF0Y2hlcicpO1xyXG4gdmFyIGNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMnKTtcclxuIHZhciBFdmVudEVtaXR0ZXIgPSByZXF1aXJlKCdldmVudHMnKS5FdmVudEVtaXR0ZXI7XHJcblxyXG52YXIgQWN0aW9uVHlwZXMgPSBjb25zdGFudHMuQWN0aW9uVHlwZXM7XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHsgIC8qKlxyXG4gICAqIFrDrXNrw6EgemUgc2VydmVydSBwb3NsZWRuw61jaCBuxJtrb2xpayBwcm9ixJtobMO9Y2ggenByw6F2IHMgdcW+aXZhdGVsZW0gcyBkYW7DvW0gaWRcclxuICAgKiBAcGFyYW0ge3N0cmluZ30gdXJsIHVybCwga3RlcsOpIHNlIHB0w6FtIG5hIHpwcsOhdnlcclxuICAgKiBAcGFyYW0ge2ludH0gdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlLCBzZSBrdGVyw71tIHNpIHDDrcWhdVxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBwYXJhbWV0ZXJzUHJlZml4IHByZWZpeCBwxZllZCBwYXJhbWV0cnkgdiB1cmxcclxuICAgKiBAcGFyYW0ge2ludH0gdXN1YWxMb2FkTWVzc2FnZXNDb3VudCAgb2J2eWtsw70gcG/EjWV0IHDFmcOtY2hvesOtY2ggenByw6F2IHYgb2Rwb3bEm2RpXHJcbiAgICovXHJcbiAgY3JlYXRlR2V0SW5pdGlhbE1lc3NhZ2VzOiBmdW5jdGlvbih1cmwsIHVzZXJDb2RlZElkLCBwYXJhbWV0ZXJzUHJlZml4LCB1c3VhbExvYWRNZXNzYWdlc0NvdW50KXtcclxuICAgIHZhciBkYXRhID0ge307XHJcbiAgXHRkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnZnJvbUlkJ10gPSB1c2VyQ29kZWRJZDtcclxuICAgIHRoaXMuYmxvY2tXaW5kb3dVbmxvYWQoJ0plxaF0xJsgc2UgbmHEjcOtdGFqw60genByw6F2eSwgb3ByYXZkdSBjaGNldGUgb2RlasOtdD8nKTtcclxuICAgIHZhciBleHBvcnRPYmplY3QgPSB0aGlzO1xyXG4gICAgJC5nZXRKU09OKHVybCwgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHtcclxuICAgICAgICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gICAgICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5OT19JTklUSUFMX01FU1NBR0VTX0FSUklWRURcclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1lbHNle1xyXG4gICAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk9MREVSX01FU1NBR0VTX0FSUklWRUQsXHJcbiAgICAgICAgICAgIGRhdGE6IHJlc3VsdCxcclxuICAgICAgICAgICAgdXNlckNvZGVkSWQgOiB1c2VyQ29kZWRJZCxcclxuICAgICAgICAgICAgdXN1YWxNZXNzYWdlc0NvdW50IDogdXN1YWxMb2FkTWVzc2FnZXNDb3VudFxyXG4gICAgICAgICAgICAvKiB0YWR5IGJ5Y2ggcMWZw61wYWRuxJsgcMWZaWRhbCBkYWzFocOtIGRhdGEgKi9cclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1cclxuICAgIH0pLmRvbmUoZnVuY3Rpb24oKSB7XHJcbiAgICAgIGV4cG9ydE9iamVjdC5yZWxvYWRXaW5kb3dVbmxvYWQoKTtcclxuICAgIH0pO1xyXG4gIH0sXHJcblxyXG4gIC8qKlxyXG4gICAqIFrDrXNrw6EgemUgc2VydmVydSBuxJtrb2xpayBzdGFyxaHDrWNoIHpwcsOhdlxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgdXJsLCBrdGVyw6kgc2UgcHTDoW0gbmEgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgb2xkZXN0SWQgaWQgbmVqc3RhcsWhw60genByw6F2eSAobmVqbWVuxaHDrSB6bsOhbcOpIGlkKVxyXG4gICAqIEBwYXJhbSAge3N0cmluZ30gcGFyYW1ldGVyc1ByZWZpeCBwcmVmaXggcMWZZWQgcGFyYW1ldHJ5IHYgdXJsXHJcbiAgICogQHBhcmFtIHtpbnR9IHVzdWFsT2xkZXJNZXNzYWdlc0NvdW50ICBvYnZ5a2zDvSBwb8SNZXQgcMWZw61jaG96w61jaCB6cHLDoXYgdiBvZHBvdsSbZGlcclxuICAgKi9cclxuICBjcmVhdGVHZXRPbGRlck1lc3NhZ2VzOiBmdW5jdGlvbih1cmwsIHVzZXJDb2RlZElkLCBvbGRlc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCwgdXN1YWxPbGRlck1lc3NhZ2VzQ291bnQpe1xyXG4gICAgdmFyIGRhdGEgPSB7fTtcclxuICBcdGRhdGFbcGFyYW1ldGVyc1ByZWZpeCArICdsYXN0SWQnXSA9IG9sZGVzdElkO1xyXG4gICAgZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ3dpdGhVc2VySWQnXSA9IHVzZXJDb2RlZElkO1xyXG4gICAgJC5nZXRKU09OKHVybCwgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHJldHVybjtcclxuICAgICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk9MREVSX01FU1NBR0VTX0FSUklWRUQsXHJcbiAgICAgICAgICBkYXRhOiByZXN1bHQsXHJcbiAgICAgICAgICB1c2VyQ29kZWRJZCA6IHVzZXJDb2RlZElkLFxyXG4gICAgICAgICAgb2xkZXJzSWQgOiBvbGRlc3RJZCxcclxuICAgICAgICAgIHVzdWFsTWVzc2FnZXNDb3VudCA6IHVzdWFsT2xkZXJNZXNzYWdlc0NvdW50XHJcbiAgICAgICAgfSk7XHJcbiAgICB9KTtcclxuICB9LFxyXG5cclxuICAvKipcclxuICAgKiBQb8WhbGUgbmEgc2VydmVyIHpwcsOhdnUuXHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IHVybCB1cmwsIGt0ZXLDqSBzZSBwdMOhbSBuYSB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gICAqIEBwYXJhbSAge1N0cmluZ30gbWVzc2FnZSB0ZXh0IHpwcsOhdnlcclxuICAgKi9cclxuICBjcmVhdGVTZW5kTWVzc2FnZTogZnVuY3Rpb24odXJsLCB1c2VyQ29kZWRJZCwgbWVzc2FnZSl7XHJcbiAgICB2YXIgZGF0YSA9IHtcclxuICAgICAgdG86IHVzZXJDb2RlZElkLFxyXG4gICAgICB0eXBlOiAndGV4dE1lc3NhZ2UnLFxyXG4gICAgICB0ZXh0OiBtZXNzYWdlXHJcbiAgICB9O1xyXG4gICAgdGhpcy5ibG9ja1dpbmRvd1VubG9hZCgnWnByw6F2YSBzZSBzdMOhbGUgb2Rlc8OtbMOhLCBwcm9zw61tZSBwb8SNa2VqdGUgbsSba29saWsgc2VrdW5kIGEgcGFrIHRvIHprdXN0ZSB6bm92YS4nKTtcclxuICAgIHZhciBleHBvcnRPYmplY3QgPSB0aGlzO1xyXG4gICAgdmFyIGpzb24gPSBKU09OLnN0cmluZ2lmeShkYXRhKTtcclxuICBcdFx0JC5hamF4KHtcclxuICBcdFx0XHRkYXRhVHlwZTogXCJqc29uXCIsXHJcbiAgXHRcdFx0dHlwZTogJ1BPU1QnLFxyXG4gIFx0XHRcdHVybDogdXJsLFxyXG4gIFx0XHRcdGRhdGE6IGpzb24sXHJcbiAgXHRcdFx0Y29udGVudFR5cGU6ICdhcHBsaWNhdGlvbi9qc29uOyBjaGFyc2V0PXV0Zi04JyxcclxuICBcdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk5FV19NRVNTQUdFU19BUlJJVkVELFxyXG4gICAgICAgICAgICBkYXRhOiByZXN1bHQsXHJcbiAgICAgICAgICAgIHVzZXJDb2RlZElkIDogdXNlckNvZGVkSWRcclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH0sXHJcbiAgICAgICAgY29tcGxldGU6IGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgICBleHBvcnRPYmplY3QucmVsb2FkV2luZG93VW5sb2FkKCk7XHJcbiAgICAgICAgfVxyXG4gIFx0XHR9KTtcclxuICB9LFxyXG5cclxuICAvKipcclxuICAgKiBaZXB0w6Egc2Ugc2VydmVydSBuYSBub3bDqSB6cHLDoXZ5XHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IHVybCB1cmwsIGt0ZXLDqSBzZSBwdMOhbSBuYSB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gICAqIEBwYXJhbSAge2ludH0gbGFzdElkIHBvc2xlZG7DrSB6bsOhbcOpIGlkXHJcbiAgICogQHBhcmFtICB7c3RyaW5nfSBwYXJhbWV0ZXJzUHJlZml4IHByZWZpeCBwxZllZCBwYXJhbWV0cnkgdiB1cmxcclxuICAgKi9cclxuICBjcmVhdGVSZWZyZXNoTWVzc2FnZXM6IGZ1bmN0aW9uKHVybCwgdXNlckNvZGVkSWQsIGxhc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCl7XHJcbiAgICB2YXIgZGF0YSA9IHt9O1xyXG4gIFx0ZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ2xhc3RpZCddID0gbGFzdElkO1xyXG4gICAgJC5nZXRKU09OKHVybCwgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHJldHVybjtcclxuICAgICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk5FV19NRVNTQUdFU19BUlJJVkVELFxyXG4gICAgICAgICAgZGF0YTogcmVzdWx0LFxyXG4gICAgICAgICAgdXNlckNvZGVkSWQgOiB1c2VyQ29kZWRJZFxyXG4gICAgICAgIH0pO1xyXG4gICAgfSk7XHJcbiAgfSxcclxuXHJcbiAgLyoqXHJcbiAgXHQgKiBQxZlpIHBva3VzdSB6YXbFmcOtdCBuZWJvIG9ibm92aXQgb2tubyBzZSB6ZXB0w6EgdcW+aXZhdGVsZSxcclxuICBcdCAqIHpkYSBjaGNlIG9rbm8gc2t1dGXEjW7EmyB6YXbFmcOtdC9vYm5vdml0LiBUb3RvIGTEm2zDoSB2IGthxb5kw6ltIHDFmcOtcGFkxJssIGRva3VkXHJcbiAgXHQgKiBzZSBuZXphdm9sw6EgcmVsb2FkV2luZG93VW5sb2FkXHJcbiAgXHQgKiBAcGFyYW0ge1N0cmluZ30gcmVhc29uIGTFr3ZvZCB1dmVkZW7DvSB2IGRpYWxvZ3VcclxuICBcdCAqL1xyXG4gIFx0YmxvY2tXaW5kb3dVbmxvYWQ6IGZ1bmN0aW9uKHJlYXNvbikge1xyXG4gIFx0XHR3aW5kb3cub25iZWZvcmV1bmxvYWQgPSBmdW5jdGlvbiAoKSB7XHJcbiAgXHRcdFx0cmV0dXJuIHJlYXNvbjtcclxuICBcdFx0fTtcclxuICBcdH0sXHJcblxyXG4gIFx0LyoqXHJcbiAgXHQgKiBWeXBuZSBobMOtZMOhbsOtIHphdsWZZW7DrS9vYm5vdmVuw60gb2tuYSBhIHZyw6F0w60gamVqIGRvIHBvxI3DoXRlxI1uw61obyBzdGF2dS5cclxuICBcdCAqL1xyXG4gIFx0cmVsb2FkV2luZG93VW5sb2FkOiBmdW5jdGlvbigpIHtcclxuICBcdFx0d2luZG93Lm9uYmVmb3JldW5sb2FkID0gZnVuY3Rpb24gKCkge1xyXG4gIFx0XHRcdHZhciB1bnNlbmQgPSBmYWxzZTtcclxuICBcdFx0XHQkLmVhY2goJChcIi5tZXNzYWdlSW5wdXRcIiksIGZ1bmN0aW9uICgpIHsvL3Byb2pkZSB2c2VjaG55IHRleHRhcmVhIGNoYXR1XHJcbiAgXHRcdFx0XHRpZiAoJC50cmltKCQodGhpcykudmFsKCkpKSB7Ly91IGthemRlaG8gemtvdW1hIGhvZG5vdHUgYmV6IHdoaXRlc3BhY3VcclxuICBcdFx0XHRcdFx0dW5zZW5kID0gdHJ1ZTtcclxuICBcdFx0XHRcdH1cclxuICBcdFx0XHR9KTtcclxuICBcdFx0XHRpZiAodW5zZW5kKSB7XHJcbiAgXHRcdFx0XHRyZXR1cm4gJ03DoXRlIHJvemVwc2Fuw70gcMWZw61zcMSbdmVrLiBDaGNldGUgdHV0byBzdHLDoW5rdSBwxZllc3RvIG9wdXN0aXQ/JztcclxuICBcdFx0XHRcdC8qIGhsw6HFoWthLCBjbyBzZSBvYmpldsOtIHDFmWkgcG9rdXN1IG9ibm92aXQvemF2xZnDrXQgb2tubywgemF0w61tY28gbcOhIHXFvml2YXRlbCByb3plcHNhbm91IHpwcsOhdnUgKi9cclxuICBcdFx0XHR9XHJcbiAgXHRcdH07XHJcbiAgXHR9XHJcbn07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcblxyXG52YXIga2V5TWlycm9yID0gcmVxdWlyZSgna2V5bWlycm9yJyk7XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuXHJcbiAgLyogdHlweSBha2PDrSwga3RlcsOpIG1vaG91IG5hc3RhdCAqL1xyXG4gIEFjdGlvblR5cGVzOiBrZXlNaXJyb3Ioe1xyXG4gICAgLyogQ0hBVCAqL1xyXG4gICAgTk9fSU5JVElBTF9NRVNTQUdFU19BUlJJVkVEIDogbnVsbCwvKiBwxZlpxaFsYSBvZHBvdsSbxI8gcMWZaSBwcnZvdG7DrW0gbmHEjcOtdMOhbsOtIHpwcsOhdiwgYWxlIGJ5bGEgcHLDoXpkbsOhKi9cclxuICAgIE9MREVSX01FU1NBR0VTX0FSUklWRUQgOiBudWxsLC8qIHDFmWnFoWx5IHN0YXLFocOtIChkb25hxI10ZW7DqSB0bGHEjcOtdGtlbSkgenByw6F2eSAqL1xyXG4gICAgTkVXX01FU1NBR0VTX0FSUklWRUQgOiBudWxsLyogcMWZacWhbHkgbm92w6kgenByw6F2eSovXHJcbiAgfSlcclxuXHJcbn07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbnZhciBEaXNwYXRjaGVyID0gcmVxdWlyZSgnZmx1eCcpLkRpc3BhdGNoZXI7XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IG5ldyBEaXNwYXRjaGVyKCk7XHJcbiIsIi8qKlxyXG4gKiBUaGlzIGZpbGUgaXMgcHJvdmlkZWQgYnkgRmFjZWJvb2sgZm9yIHRlc3RpbmcgYW5kIGV2YWx1YXRpb24gcHVycG9zZXNcclxuICogb25seS4gRmFjZWJvb2sgcmVzZXJ2ZXMgYWxsIHJpZ2h0cyBub3QgZXhwcmVzc2x5IGdyYW50ZWQuXHJcbiAqXHJcbiAqIFRIRSBTT0ZUV0FSRSBJUyBQUk9WSURFRCBcIkFTIElTXCIsIFdJVEhPVVQgV0FSUkFOVFkgT0YgQU5ZIEtJTkQsIEVYUFJFU1MgT1JcclxuICogSU1QTElFRCwgSU5DTFVESU5HIEJVVCBOT1QgTElNSVRFRCBUTyBUSEUgV0FSUkFOVElFUyBPRiBNRVJDSEFOVEFCSUxJVFksXHJcbiAqIEZJVE5FU1MgRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFIEFORCBOT05JTkZSSU5HRU1FTlQuIElOIE5PIEVWRU5UIFNIQUxMXHJcbiAqIEZBQ0VCT09LIEJFIExJQUJMRSBGT1IgQU5ZIENMQUlNLCBEQU1BR0VTIE9SIE9USEVSIExJQUJJTElUWSwgV0hFVEhFUiBJTlxyXG4gKiBBTiBBQ1RJT04gT0YgQ09OVFJBQ1QsIFRPUlQgT1IgT1RIRVJXSVNFLCBBUklTSU5HIEZST00sIE9VVCBPRiBPUiBJTlxyXG4gKiBDT05ORUNUSU9OIFdJVEggVEhFIFNPRlRXQVJFIE9SIFRIRSBVU0UgT1IgT1RIRVIgREVBTElOR1MgSU4gVEhFIFNPRlRXQVJFLlxyXG4gKi9cclxuXHJcbnZhciBEaXNwYXRjaGVyID0gcmVxdWlyZSgnLi4vLi4vZGlzcGF0Y2hlci9kYXRlbm9kZURpc3BhdGNoZXInKTtcclxudmFyIGNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMnKTtcclxudmFyIEV2ZW50RW1pdHRlciA9IHJlcXVpcmUoJ2V2ZW50cycpLkV2ZW50RW1pdHRlcjtcclxudmFyIGFzc2lnbiA9IHJlcXVpcmUoJ29iamVjdC1hc3NpZ24nKTtcclxuXHJcbnZhciBDSEFOR0VfRVZFTlQgPSAnY2hhbmdlJztcclxuXHJcbnZhciBfZGF0YVZlcnNpb24gPSAwOy8qIGtvbGlrcsOhdCBzZSB1xb4gem3Em25pbGEgZGF0YSAqL1xyXG52YXIgX21lc3NhZ2VzID0gW107XHJcbnZhciBfaW5mb01lc3NhZ2VzID0gW107XHJcbnZhciBfdGhlcmVJc01vcmUgPSB0cnVlO1xyXG5cclxudmFyIE1lc3NhZ2VTdG9yZSA9IGFzc2lnbih7fSwgRXZlbnRFbWl0dGVyLnByb3RvdHlwZSwge1xyXG4gIC8qIHRyaWdnZXIgem3Em255ICovXHJcbiAgZW1pdENoYW5nZTogZnVuY3Rpb24oKSB7XHJcbiAgICBfZGF0YVZlcnNpb24rKztcclxuICAgIGZpbHRlckluZm9NZXNzYWdlcygpO1xyXG4gICAgdGhpcy5lbWl0KENIQU5HRV9FVkVOVCk7XHJcbiAgfSxcclxuICAvKiB0b3V0byBtZXRvZG91IGx6ZSBwb3bEm3NpdCBsaXN0ZW5lciByZWFndWrDrWPDrSBwxZlpIHptxJtuxJsqL1xyXG4gIGFkZENoYW5nZUxpc3RlbmVyOiBmdW5jdGlvbihjYWxsYmFjaykge1xyXG4gICAgdGhpcy5vbihDSEFOR0VfRVZFTlQsIGNhbGxiYWNrKTtcclxuICB9LFxyXG4gIC8qIHRvdXRvIG1ldG9kb3UgbHplIGxpc3RlbmVyIG9kZWptb3V0Ki9cclxuICByZW1vdmVDaGFuZ2VMaXN0ZW5lcjogZnVuY3Rpb24oY2FsbGJhY2spIHtcclxuICAgIHRoaXMucmVtb3ZlTGlzdGVuZXIoQ0hBTkdFX0VWRU5ULCBjYWxsYmFjayk7XHJcbiAgfSxcclxuICAvKiB2cmFjw60gc3RhdiB6cHLDoXYgdiBqZWRpbsOpbSBvYmpla3R1Ki9cclxuICBnZXRTdGF0ZTogZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4ge1xyXG4gICAgICBtZXNzYWdlczogX21lc3NhZ2VzLFxyXG4gICAgICBpbmZvTWVzc2FnZXM6IF9pbmZvTWVzc2FnZXMsXHJcbiAgICAgIHRoZXJlSXNNb3JlOiBfdGhlcmVJc01vcmUsXHJcbiAgICAgIGRhdGFWZXJzaW9uOiBfZGF0YVZlcnNpb25cclxuICAgIH07XHJcbiAgfVxyXG5cclxufSk7XHJcblxyXG5NZXNzYWdlU3RvcmUuZGlzcGF0Y2hUb2tlbiA9IERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24oYWN0aW9uKSB7XHJcbiAgdmFyIHR5cGVzID0gY29uc3RhbnRzLkFjdGlvblR5cGVzO1xyXG4gIHN3aXRjaChhY3Rpb24udHlwZSl7XHJcbiAgICBjYXNlIHR5cGVzLk5FV19NRVNTQUdFU19BUlJJVkVEIDpcclxuICAgICAgYXBwZW5kRGF0YUludG9NZXNzYWdlcyhhY3Rpb24udXNlckNvZGVkSWQsIGFjdGlvbi5kYXRhLCBhY3Rpb24udXN1YWxNZXNzYWdlc0NvdW50KTtcclxuICAgICAgTWVzc2FnZVN0b3JlLmVtaXRDaGFuZ2UoKTtcclxuICAgICAgYnJlYWs7XHJcbiAgICBjYXNlIHR5cGVzLk9MREVSX01FU1NBR0VTX0FSUklWRUQgOlxyXG4gICAgICBwcmVwZW5kRGF0YUludG9NZXNzYWdlcyhhY3Rpb24udXNlckNvZGVkSWQsIGFjdGlvbi5kYXRhLCBhY3Rpb24udXN1YWxNZXNzYWdlc0NvdW50KTtcclxuICAgICAgTWVzc2FnZVN0b3JlLmVtaXRDaGFuZ2UoKTtcclxuICAgICAgYnJlYWs7XHJcbiAgICBjYXNlIHR5cGVzLk5PX0lOSVRJQUxfTUVTU0FHRVNfQVJSSVZFRDpcclxuICAgICAgTWVzc2FnZVN0b3JlLmVtaXRDaGFuZ2UoKTsvKiBrZHnFviBuZXDFmWlqZG91IMW+w6FkbsOpIHpwcsOhdnkgcMWZaSBpbmljaWFsaXphY2ksIGTDoSB0byBuYWpldm8gKi9cclxuICAgICAgYnJlYWs7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKlxyXG4gKiBOYXN0YXbDrSB6cHLDoXZ5IHplIHN0YW5kYXJkbsOtaG8gSlNPTnUgY2hhdHUgKHZpeiBkb2t1bWVudGFjZSkgZG8gc3RhdnUgdG9ob3RvIFN0b3JlIHphIGV4aXN0dWrDrWPDrSB6cHLDoXZ5LlxyXG4gKiBAcGFyYW0gIHtpbnR9IHVzZXJDb2RlZElkIGlkIHXFvml2YXRlbGUsIG9kIGt0ZXLDqWhvIGNoY2kgbmHEjcOtc3QgenByw6F2eVxyXG4gKiBAcGFyYW0gIHtqc29ufSBqc29uRGF0YSAgZGF0YSB6ZSBzZXJ2ZXJ1XHJcbiAqL1xyXG52YXIgYXBwZW5kRGF0YUludG9NZXNzYWdlcyA9IGZ1bmN0aW9uKHVzZXJDb2RlZElkLCBqc29uRGF0YSl7XHJcbiAgdmFyIHJlc3VsdCA9IGpzb25EYXRhW3VzZXJDb2RlZElkXTtcclxuICBfbWVzc2FnZXMgPSBfbWVzc2FnZXMuY29uY2F0KHJlc3VsdC5tZXNzYWdlcyk7XHJcbn07XHJcblxyXG4vKipcclxuICogTmFzdGF2w60genByw6F2eSB6ZSBzdGFuZGFyZG7DrWhvIEpTT051IGNoYXR1ICh2aXogZG9rdW1lbnRhY2UpIGRvIHN0YXZ1IHRvaG90byBTdG9yZSBwxZllZCBleGlzdHVqw61jw60genByw6F2eS5cclxuICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICogQHBhcmFtICB7anNvbn0ganNvbkRhdGEgIGRhdGEgemUgc2VydmVydVxyXG4gKiBAcGFyYW0gIHtpbnR9IHVzdWFsTWVzc2FnZXNDb3VudCBvYnZ5a2zDvSBwb8SNZXQgenByw6F2IC0gcG9rdWQgamUgZG9kcsW+ZW4sIHphaG9kw60gbmVqc3RhcsWhw60genByw6F2dSAocG9rdWQgamUgenByw6F2IGRvc3RhdGVrKVxyXG4gKiBhIGtvbXBvbmVudMSbIHBvZGxlIHRvaG8gbmFzdGF2w60gc3Rhdiwgxb5lIG5hIHNlcnZlcnUgamXFoXTEmyBqc291L3XFviBuZWpzb3UgZGFsxaHDrSB6cHLDoXZ5XHJcbiAqL1xyXG52YXIgcHJlcGVuZERhdGFJbnRvTWVzc2FnZXMgPSBmdW5jdGlvbih1c2VyQ29kZWRJZCwganNvbkRhdGEsIHVzdWFsTWVzc2FnZXNDb3VudCl7XHJcbiAgdmFyIHRoZXJlSXNNb3JlID0gdHJ1ZTtcclxuICB2YXIgcmVzdWx0ID0ganNvbkRhdGFbdXNlckNvZGVkSWRdO1xyXG4gIGlmKHJlc3VsdC5tZXNzYWdlcy5sZW5ndGggPCB1c3VhbE1lc3NhZ2VzQ291bnQpey8qIHBva3VkIG3DoW0gbcOpbsSbIHpwcsOhdiBuZcW+IGplIG9idnlrbMOpKi9cclxuICAgIHRoZXJlSXNNb3JlID0gZmFsc2U7XHJcbiAgfWVsc2V7XHJcbiAgICByZXN1bHQubWVzc2FnZXMuc2hpZnQoKTsvKiBvZGViZXJ1IHBydm7DrSB6cHLDoXZ1ICovXHJcbiAgfVxyXG4gIF90aGVyZUlzTW9yZSA9IHRoZXJlSXNNb3JlO1xyXG4gIF9tZXNzYWdlcyA9IHJlc3VsdC5tZXNzYWdlcy5jb25jYXQoX21lc3NhZ2VzKTtcclxufTtcclxuXHJcbi8qKlxyXG4gKiBPZGZpbHRydWplIHogZGF0IGluZm96cHLDoXZ5IGEgdnl0xZnDrWTDrSBqZSB6dmzDocWhxaVcclxuICogQHJldHVybiB7W3R5cGVdfSBbZGVzY3JpcHRpb25dXHJcbiAqL1xyXG52YXIgZmlsdGVySW5mb01lc3NhZ2VzID0gZnVuY3Rpb24oKXtcclxuICB2YXIgY2xlYXJNb2RlID0gZmFsc2U7IC8qIHBvIHDFmWVwbnV0w60gZG8gxI1pxaF0xJtuw60gamVuIHZ5aGF6dWplIGluZm96cHLDoXZ5Ki9cclxuICBmb3IodmFyIGkgPSBfbWVzc2FnZXMubGVuZ3RoIC0gMSA7IGkgPj0gTWF0aC5tYXgoMCwgX21lc3NhZ2VzLmxlbmd0aCAtIDIwKTsgaS0tKXsvKnByb2pkZSB6cHLDoXZ5IHNob3JhIGRvbMWvIC0gamVuIHBvc2xlZG7DrWNoIGR2YWNldCBrdsWvbGkgdsO9a29udSovXHJcbiAgICBpZihfbWVzc2FnZXNbaV0udHlwZSA9PSAxKXsvKiBrZHnFviBqZSB0byBpbmZvenByw6F2YSAqL1xyXG4gICAgICBpZighY2xlYXJNb2RlKXsvKiBuZcSNaXN0w60gc2UgKi9cclxuICAgICAgICBhZGRUb0luZm9NZXNzYWdlcyhfbWVzc2FnZXNbaV0pO1xyXG4gICAgICB9XHJcbiAgICAgIF9tZXNzYWdlcy5zcGxpY2UoaSwxKTsvKiBvZHN0cmFuxJtuw60genByw6F2eSAqL1xyXG4gICAgfWVsc2V7XHJcbiAgICAgIGNsZWFyTW9kZSA9IHRydWU7Lyoga2R5xb4gbmFqZGUgbm9ybcOhbG7DrSB6cHLDoXZ1LCB2ecSNaXN0w60gdsWhZWNobnkgaW5mb3pwcsOhdnkgdsO9xaFlICovXHJcbiAgICB9XHJcbiAgfVxyXG59O1xyXG5cclxuLyoqXHJcbiAqIFDFmWlkw6EgenByw6F2dSBrIGluZm96cHLDoXbDoW0sIHBva3VkIG1lemkgbmltaSBqZcWhdMSbIG5lbsOtXHJcbiAqIEBwYXJhbSAge2pzb259IG1lc3NhZ2UgenByw6F2YSBwxZlpamF0w6EgemUgc2VydmVydVxyXG4gKi9cclxudmFyIGFkZFRvSW5mb01lc3NhZ2VzID0gZnVuY3Rpb24obWVzc2FnZSkge1xyXG4gIHZhciBhbHJlYWR5RXhpc3RzID0gZmFsc2U7XHJcbiAgX2luZm9NZXNzYWdlcy5mb3JFYWNoKGZ1bmN0aW9uKGluZm9NZXNzYWdlKXtcclxuICAgIGlmKGluZm9NZXNzYWdlLnRleHQgPT0gbWVzc2FnZS50ZXh0KXtcclxuICAgICAgYWxyZWFkeUV4aXN0cyA9IHRydWU7XHJcbiAgICAgIHJldHVybjtcclxuICAgIH1cclxuICB9KTtcclxuICBpZighYWxyZWFkeUV4aXN0cyl7XHJcbiAgICBfaW5mb01lc3NhZ2VzLnB1c2gobWVzc2FnZSk7XHJcbiAgfVxyXG59O1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBNZXNzYWdlU3RvcmU7XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5cclxuLyoqKioqKioqKioqICBJTklDSUFMSVpBQ0UgICoqKioqKioqKioqL1xyXG52YXIgY2hhdFJvb3QgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0V2luZG93Jyk7XHJcbmlmKHR5cGVvZihjaGF0Um9vdCkgIT0gJ3VuZGVmaW5lZCcgJiYgY2hhdFJvb3QgIT0gbnVsbCl7LypleGlzdHVqZSBlbGVtZW50IHBybyBjaGF0Ki9cclxuICB2YXIgQ2hhdCA9IHJlcXVpcmUoJy4vY2hhdC9yZWFjdENoYXQnKTtcclxuICB2YXIgbG9nZ2VkVXNlciA9IHtcclxuICAgIG5hbWU6IGNoYXRSb290LmRhdGFzZXQudXNlcm5hbWUsXHJcbiAgICBocmVmOiBjaGF0Um9vdC5kYXRhc2V0LnVzZXJocmVmLFxyXG4gICAgcHJvZmlsZVBob3RvVXJsOiBjaGF0Um9vdC5kYXRhc2V0LnByb2ZpbGVwaG90b3VybFxyXG4gIH07XHJcbiAgUmVhY3QucmVuZGVyKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KENoYXQuQ2hhdFdpbmRvdywge3VzZXJDb2RlZElkOiBjaGF0Um9vdC5kYXRhc2V0LnVzZXJpbmNoYXRjb2RlZGlkLCBsb2dnZWRVc2VyOiBsb2dnZWRVc2VyfSksXHJcbiAgICAgIGNoYXRSb290XHJcbiAgKTtcclxufVxyXG4iXX0=
