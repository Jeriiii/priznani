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
var MessageConstants = require('../flux/constants/ChatConstants').MessageConstants;
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
        messages.map(function(message, i){
            return React.createElement(Message, {key: userCodedId + 'message' + i, messageData: message, userHref: message.profileHref, profilePhotoUrl: message.profilePhotoUrl});
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
          message.images.map(function(image, i){
                return React.createElement("img", {src: image.url, width: image.width, key: message.id + 'message' + i});
            }), 
          
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
        "Načíst předchozí zprávy"
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
    var slapButton = '';
    console.log(loggedUser);
    if (loggedUser.allowedToSlap){
      slapButton = React.createElement("a", {href: "#", title: "Poslat facku", className: "sendSlap", onClick: this.sendSlap})
    }
    return (
      React.createElement("div", {className: "newMessage"}, 
        React.createElement(ProfilePhoto, {profileLink: loggedUser.href, userName: loggedUser.name, profilePhotoUrl: loggedUser.profilePhotoUrl}), 
        React.createElement("div", {className: "messageArrow"}), 
        React.createElement("form", {onSubmit: this.onSubmit}, 
          React.createElement("div", {className: "messageInputContainer"}, 
            React.createElement("input", {type: "text", className: "messageInput"}), 
            React.createElement("div", {className: "inputInterface"}, 
              slapButton
            ), 
            React.createElement("div", {className: "clear"})
          ), 
          React.createElement("input", {type: "submit", className: "btn-main medium button", value: "Odeslat"})
        )
      )
    );
  },
  sendSlap: function(e){
    e.preventDefault();
    MessageActions.createSendMessage(reactSendMessageLink, this.props.userCodedId, MessageConstants.SEND_SLAP, getLastId());
  },
  onSubmit: function(e){/* Vezme zprávu ze submitu a pošle ji. Také smaže zprávu napsanou v inputu. */
    e.preventDefault();
    var input = e.target.getElementsByClassName('messageInput')[0];
    var message = input.value;
    if(message == undefined || message.trim() == '') return;
    input.value = '';
    MessageActions.createSendMessage(reactSendMessageLink, this.props.userCodedId, message, getLastId());
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
      Timer.lastId = getLastId();
      Timer.tick = function(){
        MessageActions.createRefreshMessages(reactRefreshMessagesLink, userCodedId, Timer.lastId, parametersPrefix);
      };
      Timer.start();
    }else{/* když se data nezměnila poprvé, ale určitě se změnila */
      Timer.lastId = getLastId();
      Timer.resetTime();
    }
  });

};

/**
 * Vrátí poslední známé id
 * @return {int} posledni známé id
 */
var getLastId = function() {
  var state = MessageStore.getState();
  if(state.messages.length > 0){
    return state.messages[state.messages.length - 1].id;
  }else{
    return 0;
  }
}

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

},{"../components/profile":8,"../components/timer":9,"../flux/actions/chat/MessageActionCreators":10,"../flux/constants/ChatConstants":12,"../flux/stores/chat/MessageStore":14}],8:[function(require,module,exports){
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

var ActionTypes = constants.ActionTypes
/* zamykání ošetřující souběžné poslání požadavku */
var ajaxLock = false;

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
    }).fail(function(){
      dispatcher.dispatch({
        type: ActionTypes.MESSAGE_ERROR,
        errorMessage: 'Zprávy se bohužel nepodařilo načíst. Zkuste to znovu později.'
      });
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
    ajaxLock = true;
    var data = {};
  	data[parametersPrefix + 'lastId'] = oldestId;
    data[parametersPrefix + 'withUserId'] = userCodedId;
    $.getJSON(url, data, function(result){
        ajaxLock = false;
        if(result.length == 0) return;
        dispatcher.dispatch({
          type: ActionTypes.OLDER_MESSAGES_ARRIVED,
          data: result,
          userCodedId : userCodedId,
          oldersId : oldestId,
          usualMessagesCount : usualOlderMessagesCount
        });
    }).fail(function(){
      dispatcher.dispatch({
        type: ActionTypes.MESSAGE_ERROR,
        errorMessage: 'Zprávy se bohužel nepodařilo načíst. Zkuste to znovu později.'
      });
    });
  },

  /**
   * Pošle na server zprávu.
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {String} message text zprávy
   * @param  {int} lastId poslední známé id
   */
  createSendMessage: function(url, userCodedId, message, lastId){
    ajaxLock = true;
    var data = {
      to: userCodedId,
      type: 'textMessage',
      text: message,
      lastid: lastId
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
          ajaxLock = false;
          exportObject.reloadWindowUnload();
        },
        error: function(){
          dispatcher.dispatch({
            type: ActionTypes.MESSAGE_ERROR,
            errorMessage: 'Vaši zprávu se bohužel nepodařilo odeslat. Zkuste to znovu později.'
          });
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
    if(ajaxLock) return;
    var data = {};
  	data[parametersPrefix + 'lastid'] = lastId;
    data[parametersPrefix + 'readedMessages'] = [lastId];
    $.getJSON(url, data, function(result){
        if(result.length == 0) return;
        dispatcher.dispatch({
          type: ActionTypes.NEW_MESSAGES_ARRIVED,
          data: result,
          userCodedId : userCodedId
        });
    }).fail(function(){
      dispatcher.dispatch({
        type: ActionTypes.MESSAGE_ERROR,
        errorMessage: 'Zprávy se bohužel nepodařilo načíst. Zkuste to znovu později.'
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

},{"../../constants/ActionConstants":11,"../../dispatcher/datenodeDispatcher":13,"events":6}],11:[function(require,module,exports){
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
    NEW_MESSAGES_ARRIVED : null,/* přišly nové zprávy*/
    MESSAGE_ERROR : null /* něco se nepovedlo */
  })

};

},{"keymirror":4}],12:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */


module.exports = {

  /* speciální řetězce rozlišované chatem */
  MessageConstants: {
    SEND_SLAP : '@!slap444',
  }

};

},{}],13:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

var Dispatcher = require('flux').Dispatcher;

module.exports = new Dispatcher();

},{"flux":1}],14:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

var Dispatcher = require('../../dispatcher/datenodeDispatcher');
if(typeof jest !== 'undefined'){
   jest.autoMockOff();/* obezlička kvůli testování */
   var constants = require('../../constants/ActionConstants');
   jest.autoMockOn();
}else{
  var constants = require('../../constants/ActionConstants');
}
var MessageConstants = require('../../constants/ChatConstants').MessageConstants;


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
    if(_messages.length == 0) _thereIsMore = false;
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
    case types.MESSAGE_ERROR:
      alert('Chyba sítě: ' + action.errorMessage);
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
  var resultMessages = filterInfoMessages(result.messages);
  resultMessages = modifyMessages(resultMessages);
  _messages = _messages.concat(resultMessages);
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
  var textMessages = filterInfoMessages(result.messages)
  result.messages = modifyMessages(textMessages);
  _messages = result.messages.concat(_messages);
};

/**
 * Odfiltruje z dat infozprávy a vytřídí je zvlášť do globální proměnné
 * @param {json} messages zprávy přijaté ze serveru
 */
var filterInfoMessages = function(messages){
  _infoMessages = [];
  for(var i = 0; i < messages.length; i++){
    if(messages[i].type == 1){/* když je to infozpráva */
      addToInfoMessages(messages[i]);
      messages.splice(i,1);/* odstranění zprávy */
    }
  }
  return messages;
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
  /**
   * Modifikuje text daných zpráv (sem patří zejména nahrazování určitých částí obrázkem - smajlíky, facky, poslané url obrázku...)
   * @param  {Object} messages sada zpráv
   */
  var modifyMessages = function(messages) {
    messages.forEach(function(message){
      message.images = [];
      /* nahrazení speciálního symbolu obrázkem */
      checkSlap(message);
    });
    return messages;
  };

  /**
   * Zkontroluje, zda zpráva neobsahuje symbol facky
   * @param  {Object} message objekt jedné zprávy
   */
  var checkSlap = function(message){
    if (message.text.indexOf(MessageConstants.SEND_SLAP) >= 0){/* obsahuje symbol facky */
      message.images.push({/* přidání facky do pole obrázků */
        url: '../images/chatContent/slap-image.png',
        width: '256'
      });
      message.text = message.text.replace(new RegExp(MessageConstants.SEND_SLAP, 'g'), '');/* smazání všech stringů pro facku */
    }
  }

module.exports = MessageStore;

},{"../../constants/ActionConstants":11,"../../constants/ChatConstants":12,"../../dispatcher/datenodeDispatcher":13,"events":6,"object-assign":5}],15:[function(require,module,exports){
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
    allowedToSlap: (chatRoot.dataset.canslap == 'true'),
    href: chatRoot.dataset.userhref,
    profilePhotoUrl: chatRoot.dataset.profilephotourl
  };
  React.render(
      React.createElement(Chat.ChatWindow, {userCodedId: chatRoot.dataset.userinchatcodedid, loggedUser: loggedUser}),
      chatRoot
  );
}

},{"./chat/reactChat":7}]},{},[15])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwibm9kZV9tb2R1bGVzL2ZsdXgvaW5kZXguanMiLCJub2RlX21vZHVsZXMvZmx1eC9saWIvRGlzcGF0Y2hlci5qcyIsIm5vZGVfbW9kdWxlcy9mbHV4L2xpYi9pbnZhcmlhbnQuanMiLCJub2RlX21vZHVsZXMva2V5bWlycm9yL2luZGV4LmpzIiwibm9kZV9tb2R1bGVzL29iamVjdC1hc3NpZ24vaW5kZXguanMiLCJub2RlX21vZHVsZXMvd2F0Y2hpZnkvbm9kZV9tb2R1bGVzL2Jyb3dzZXJpZnkvbm9kZV9tb2R1bGVzL2V2ZW50cy9ldmVudHMuanMiLCJzcmMvY2hhdC9yZWFjdENoYXQuanMiLCJzcmMvY29tcG9uZW50cy9wcm9maWxlLmpzIiwic3JjL2NvbXBvbmVudHMvdGltZXIuanMiLCJzcmMvZmx1eC9hY3Rpb25zL2NoYXQvTWVzc2FnZUFjdGlvbkNyZWF0b3JzLmpzIiwic3JjL2ZsdXgvY29uc3RhbnRzL0FjdGlvbkNvbnN0YW50cy5qcyIsInNyYy9mbHV4L2NvbnN0YW50cy9DaGF0Q29uc3RhbnRzLmpzIiwic3JjL2ZsdXgvZGlzcGF0Y2hlci9kYXRlbm9kZURpc3BhdGNoZXIuanMiLCJzcmMvZmx1eC9zdG9yZXMvY2hhdC9NZXNzYWdlU3RvcmUuanMiLCJzcmMvcmVhY3REYXRlbm9kZS5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDVkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxUEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDN1NBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JOQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNsREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdkxBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2RBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNSQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNyS0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCIvKipcclxuICogQ29weXJpZ2h0IChjKSAyMDE0LTIwMTUsIEZhY2Vib29rLCBJbmMuXHJcbiAqIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqXHJcbiAqIFRoaXMgc291cmNlIGNvZGUgaXMgbGljZW5zZWQgdW5kZXIgdGhlIEJTRC1zdHlsZSBsaWNlbnNlIGZvdW5kIGluIHRoZVxyXG4gKiBMSUNFTlNFIGZpbGUgaW4gdGhlIHJvb3QgZGlyZWN0b3J5IG9mIHRoaXMgc291cmNlIHRyZWUuIEFuIGFkZGl0aW9uYWwgZ3JhbnRcclxuICogb2YgcGF0ZW50IHJpZ2h0cyBjYW4gYmUgZm91bmQgaW4gdGhlIFBBVEVOVFMgZmlsZSBpbiB0aGUgc2FtZSBkaXJlY3RvcnkuXHJcbiAqL1xyXG5cclxubW9kdWxlLmV4cG9ydHMuRGlzcGF0Y2hlciA9IHJlcXVpcmUoJy4vbGliL0Rpc3BhdGNoZXInKVxyXG4iLCIvKlxyXG4gKiBDb3B5cmlnaHQgKGMpIDIwMTQsIEZhY2Vib29rLCBJbmMuXHJcbiAqIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqXHJcbiAqIFRoaXMgc291cmNlIGNvZGUgaXMgbGljZW5zZWQgdW5kZXIgdGhlIEJTRC1zdHlsZSBsaWNlbnNlIGZvdW5kIGluIHRoZVxyXG4gKiBMSUNFTlNFIGZpbGUgaW4gdGhlIHJvb3QgZGlyZWN0b3J5IG9mIHRoaXMgc291cmNlIHRyZWUuIEFuIGFkZGl0aW9uYWwgZ3JhbnRcclxuICogb2YgcGF0ZW50IHJpZ2h0cyBjYW4gYmUgZm91bmQgaW4gdGhlIFBBVEVOVFMgZmlsZSBpbiB0aGUgc2FtZSBkaXJlY3RvcnkuXHJcbiAqXHJcbiAqIEBwcm92aWRlc01vZHVsZSBEaXNwYXRjaGVyXHJcbiAqIEB0eXBlY2hlY2tzXHJcbiAqL1xyXG5cclxuXCJ1c2Ugc3RyaWN0XCI7XHJcblxyXG52YXIgaW52YXJpYW50ID0gcmVxdWlyZSgnLi9pbnZhcmlhbnQnKTtcclxuXHJcbnZhciBfbGFzdElEID0gMTtcclxudmFyIF9wcmVmaXggPSAnSURfJztcclxuXHJcbi8qKlxyXG4gKiBEaXNwYXRjaGVyIGlzIHVzZWQgdG8gYnJvYWRjYXN0IHBheWxvYWRzIHRvIHJlZ2lzdGVyZWQgY2FsbGJhY2tzLiBUaGlzIGlzXHJcbiAqIGRpZmZlcmVudCBmcm9tIGdlbmVyaWMgcHViLXN1YiBzeXN0ZW1zIGluIHR3byB3YXlzOlxyXG4gKlxyXG4gKiAgIDEpIENhbGxiYWNrcyBhcmUgbm90IHN1YnNjcmliZWQgdG8gcGFydGljdWxhciBldmVudHMuIEV2ZXJ5IHBheWxvYWQgaXNcclxuICogICAgICBkaXNwYXRjaGVkIHRvIGV2ZXJ5IHJlZ2lzdGVyZWQgY2FsbGJhY2suXHJcbiAqICAgMikgQ2FsbGJhY2tzIGNhbiBiZSBkZWZlcnJlZCBpbiB3aG9sZSBvciBwYXJ0IHVudGlsIG90aGVyIGNhbGxiYWNrcyBoYXZlXHJcbiAqICAgICAgYmVlbiBleGVjdXRlZC5cclxuICpcclxuICogRm9yIGV4YW1wbGUsIGNvbnNpZGVyIHRoaXMgaHlwb3RoZXRpY2FsIGZsaWdodCBkZXN0aW5hdGlvbiBmb3JtLCB3aGljaFxyXG4gKiBzZWxlY3RzIGEgZGVmYXVsdCBjaXR5IHdoZW4gYSBjb3VudHJ5IGlzIHNlbGVjdGVkOlxyXG4gKlxyXG4gKiAgIHZhciBmbGlnaHREaXNwYXRjaGVyID0gbmV3IERpc3BhdGNoZXIoKTtcclxuICpcclxuICogICAvLyBLZWVwcyB0cmFjayBvZiB3aGljaCBjb3VudHJ5IGlzIHNlbGVjdGVkXHJcbiAqICAgdmFyIENvdW50cnlTdG9yZSA9IHtjb3VudHJ5OiBudWxsfTtcclxuICpcclxuICogICAvLyBLZWVwcyB0cmFjayBvZiB3aGljaCBjaXR5IGlzIHNlbGVjdGVkXHJcbiAqICAgdmFyIENpdHlTdG9yZSA9IHtjaXR5OiBudWxsfTtcclxuICpcclxuICogICAvLyBLZWVwcyB0cmFjayBvZiB0aGUgYmFzZSBmbGlnaHQgcHJpY2Ugb2YgdGhlIHNlbGVjdGVkIGNpdHlcclxuICogICB2YXIgRmxpZ2h0UHJpY2VTdG9yZSA9IHtwcmljZTogbnVsbH1cclxuICpcclxuICogV2hlbiBhIHVzZXIgY2hhbmdlcyB0aGUgc2VsZWN0ZWQgY2l0eSwgd2UgZGlzcGF0Y2ggdGhlIHBheWxvYWQ6XHJcbiAqXHJcbiAqICAgZmxpZ2h0RGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAqICAgICBhY3Rpb25UeXBlOiAnY2l0eS11cGRhdGUnLFxyXG4gKiAgICAgc2VsZWN0ZWRDaXR5OiAncGFyaXMnXHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoaXMgcGF5bG9hZCBpcyBkaWdlc3RlZCBieSBgQ2l0eVN0b3JlYDpcclxuICpcclxuICogICBmbGlnaHREaXNwYXRjaGVyLnJlZ2lzdGVyKGZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICogICAgIGlmIChwYXlsb2FkLmFjdGlvblR5cGUgPT09ICdjaXR5LXVwZGF0ZScpIHtcclxuICogICAgICAgQ2l0eVN0b3JlLmNpdHkgPSBwYXlsb2FkLnNlbGVjdGVkQ2l0eTtcclxuICogICAgIH1cclxuICogICB9KTtcclxuICpcclxuICogV2hlbiB0aGUgdXNlciBzZWxlY3RzIGEgY291bnRyeSwgd2UgZGlzcGF0Y2ggdGhlIHBheWxvYWQ6XHJcbiAqXHJcbiAqICAgZmxpZ2h0RGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAqICAgICBhY3Rpb25UeXBlOiAnY291bnRyeS11cGRhdGUnLFxyXG4gKiAgICAgc2VsZWN0ZWRDb3VudHJ5OiAnYXVzdHJhbGlhJ1xyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBUaGlzIHBheWxvYWQgaXMgZGlnZXN0ZWQgYnkgYm90aCBzdG9yZXM6XHJcbiAqXHJcbiAqICAgIENvdW50cnlTdG9yZS5kaXNwYXRjaFRva2VuID0gZmxpZ2h0RGlzcGF0Y2hlci5yZWdpc3RlcihmdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAqICAgICBpZiAocGF5bG9hZC5hY3Rpb25UeXBlID09PSAnY291bnRyeS11cGRhdGUnKSB7XHJcbiAqICAgICAgIENvdW50cnlTdG9yZS5jb3VudHJ5ID0gcGF5bG9hZC5zZWxlY3RlZENvdW50cnk7XHJcbiAqICAgICB9XHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFdoZW4gdGhlIGNhbGxiYWNrIHRvIHVwZGF0ZSBgQ291bnRyeVN0b3JlYCBpcyByZWdpc3RlcmVkLCB3ZSBzYXZlIGEgcmVmZXJlbmNlXHJcbiAqIHRvIHRoZSByZXR1cm5lZCB0b2tlbi4gVXNpbmcgdGhpcyB0b2tlbiB3aXRoIGB3YWl0Rm9yKClgLCB3ZSBjYW4gZ3VhcmFudGVlXHJcbiAqIHRoYXQgYENvdW50cnlTdG9yZWAgaXMgdXBkYXRlZCBiZWZvcmUgdGhlIGNhbGxiYWNrIHRoYXQgdXBkYXRlcyBgQ2l0eVN0b3JlYFxyXG4gKiBuZWVkcyB0byBxdWVyeSBpdHMgZGF0YS5cclxuICpcclxuICogICBDaXR5U3RvcmUuZGlzcGF0Y2hUb2tlbiA9IGZsaWdodERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gKiAgICAgaWYgKHBheWxvYWQuYWN0aW9uVHlwZSA9PT0gJ2NvdW50cnktdXBkYXRlJykge1xyXG4gKiAgICAgICAvLyBgQ291bnRyeVN0b3JlLmNvdW50cnlgIG1heSBub3QgYmUgdXBkYXRlZC5cclxuICogICAgICAgZmxpZ2h0RGlzcGF0Y2hlci53YWl0Rm9yKFtDb3VudHJ5U3RvcmUuZGlzcGF0Y2hUb2tlbl0pO1xyXG4gKiAgICAgICAvLyBgQ291bnRyeVN0b3JlLmNvdW50cnlgIGlzIG5vdyBndWFyYW50ZWVkIHRvIGJlIHVwZGF0ZWQuXHJcbiAqXHJcbiAqICAgICAgIC8vIFNlbGVjdCB0aGUgZGVmYXVsdCBjaXR5IGZvciB0aGUgbmV3IGNvdW50cnlcclxuICogICAgICAgQ2l0eVN0b3JlLmNpdHkgPSBnZXREZWZhdWx0Q2l0eUZvckNvdW50cnkoQ291bnRyeVN0b3JlLmNvdW50cnkpO1xyXG4gKiAgICAgfVxyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBUaGUgdXNhZ2Ugb2YgYHdhaXRGb3IoKWAgY2FuIGJlIGNoYWluZWQsIGZvciBleGFtcGxlOlxyXG4gKlxyXG4gKiAgIEZsaWdodFByaWNlU3RvcmUuZGlzcGF0Y2hUb2tlbiA9XHJcbiAqICAgICBmbGlnaHREaXNwYXRjaGVyLnJlZ2lzdGVyKGZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICogICAgICAgc3dpdGNoIChwYXlsb2FkLmFjdGlvblR5cGUpIHtcclxuICogICAgICAgICBjYXNlICdjb3VudHJ5LXVwZGF0ZSc6XHJcbiAqICAgICAgICAgICBmbGlnaHREaXNwYXRjaGVyLndhaXRGb3IoW0NpdHlTdG9yZS5kaXNwYXRjaFRva2VuXSk7XHJcbiAqICAgICAgICAgICBGbGlnaHRQcmljZVN0b3JlLnByaWNlID1cclxuICogICAgICAgICAgICAgZ2V0RmxpZ2h0UHJpY2VTdG9yZShDb3VudHJ5U3RvcmUuY291bnRyeSwgQ2l0eVN0b3JlLmNpdHkpO1xyXG4gKiAgICAgICAgICAgYnJlYWs7XHJcbiAqXHJcbiAqICAgICAgICAgY2FzZSAnY2l0eS11cGRhdGUnOlxyXG4gKiAgICAgICAgICAgRmxpZ2h0UHJpY2VTdG9yZS5wcmljZSA9XHJcbiAqICAgICAgICAgICAgIEZsaWdodFByaWNlU3RvcmUoQ291bnRyeVN0b3JlLmNvdW50cnksIENpdHlTdG9yZS5jaXR5KTtcclxuICogICAgICAgICAgIGJyZWFrO1xyXG4gKiAgICAgfVxyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBUaGUgYGNvdW50cnktdXBkYXRlYCBwYXlsb2FkIHdpbGwgYmUgZ3VhcmFudGVlZCB0byBpbnZva2UgdGhlIHN0b3JlcydcclxuICogcmVnaXN0ZXJlZCBjYWxsYmFja3MgaW4gb3JkZXI6IGBDb3VudHJ5U3RvcmVgLCBgQ2l0eVN0b3JlYCwgdGhlblxyXG4gKiBgRmxpZ2h0UHJpY2VTdG9yZWAuXHJcbiAqL1xyXG5cclxuICBmdW5jdGlvbiBEaXNwYXRjaGVyKCkge1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3MgPSB7fTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nID0ge307XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzSGFuZGxlZCA9IHt9O1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nID0gZmFsc2U7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX3BlbmRpbmdQYXlsb2FkID0gbnVsbDtcclxuICB9XHJcblxyXG4gIC8qKlxyXG4gICAqIFJlZ2lzdGVycyBhIGNhbGxiYWNrIHRvIGJlIGludm9rZWQgd2l0aCBldmVyeSBkaXNwYXRjaGVkIHBheWxvYWQuIFJldHVybnNcclxuICAgKiBhIHRva2VuIHRoYXQgY2FuIGJlIHVzZWQgd2l0aCBgd2FpdEZvcigpYC5cclxuICAgKlxyXG4gICAqIEBwYXJhbSB7ZnVuY3Rpb259IGNhbGxiYWNrXHJcbiAgICogQHJldHVybiB7c3RyaW5nfVxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLnJlZ2lzdGVyPWZ1bmN0aW9uKGNhbGxiYWNrKSB7XHJcbiAgICB2YXIgaWQgPSBfcHJlZml4ICsgX2xhc3RJRCsrO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3NbaWRdID0gY2FsbGJhY2s7XHJcbiAgICByZXR1cm4gaWQ7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogUmVtb3ZlcyBhIGNhbGxiYWNrIGJhc2VkIG9uIGl0cyB0b2tlbi5cclxuICAgKlxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBpZFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLnVucmVnaXN0ZXI9ZnVuY3Rpb24oaWQpIHtcclxuICAgIGludmFyaWFudChcclxuICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3NbaWRdLFxyXG4gICAgICAnRGlzcGF0Y2hlci51bnJlZ2lzdGVyKC4uLik6IGAlc2AgZG9lcyBub3QgbWFwIHRvIGEgcmVnaXN0ZXJlZCBjYWxsYmFjay4nLFxyXG4gICAgICBpZFxyXG4gICAgKTtcclxuICAgIGRlbGV0ZSB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF07XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogV2FpdHMgZm9yIHRoZSBjYWxsYmFja3Mgc3BlY2lmaWVkIHRvIGJlIGludm9rZWQgYmVmb3JlIGNvbnRpbnVpbmcgZXhlY3V0aW9uXHJcbiAgICogb2YgdGhlIGN1cnJlbnQgY2FsbGJhY2suIFRoaXMgbWV0aG9kIHNob3VsZCBvbmx5IGJlIHVzZWQgYnkgYSBjYWxsYmFjayBpblxyXG4gICAqIHJlc3BvbnNlIHRvIGEgZGlzcGF0Y2hlZCBwYXlsb2FkLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHthcnJheTxzdHJpbmc+fSBpZHNcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS53YWl0Rm9yPWZ1bmN0aW9uKGlkcykge1xyXG4gICAgaW52YXJpYW50KFxyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmcsXHJcbiAgICAgICdEaXNwYXRjaGVyLndhaXRGb3IoLi4uKTogTXVzdCBiZSBpbnZva2VkIHdoaWxlIGRpc3BhdGNoaW5nLidcclxuICAgICk7XHJcbiAgICBmb3IgKHZhciBpaSA9IDA7IGlpIDwgaWRzLmxlbmd0aDsgaWkrKykge1xyXG4gICAgICB2YXIgaWQgPSBpZHNbaWldO1xyXG4gICAgICBpZiAodGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmdbaWRdKSB7XHJcbiAgICAgICAgaW52YXJpYW50KFxyXG4gICAgICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0hhbmRsZWRbaWRdLFxyXG4gICAgICAgICAgJ0Rpc3BhdGNoZXIud2FpdEZvciguLi4pOiBDaXJjdWxhciBkZXBlbmRlbmN5IGRldGVjdGVkIHdoaWxlICcgK1xyXG4gICAgICAgICAgJ3dhaXRpbmcgZm9yIGAlc2AuJyxcclxuICAgICAgICAgIGlkXHJcbiAgICAgICAgKTtcclxuICAgICAgICBjb250aW51ZTtcclxuICAgICAgfVxyXG4gICAgICBpbnZhcmlhbnQoXHJcbiAgICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3NbaWRdLFxyXG4gICAgICAgICdEaXNwYXRjaGVyLndhaXRGb3IoLi4uKTogYCVzYCBkb2VzIG5vdCBtYXAgdG8gYSByZWdpc3RlcmVkIGNhbGxiYWNrLicsXHJcbiAgICAgICAgaWRcclxuICAgICAgKTtcclxuICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9pbnZva2VDYWxsYmFjayhpZCk7XHJcbiAgICB9XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogRGlzcGF0Y2hlcyBhIHBheWxvYWQgdG8gYWxsIHJlZ2lzdGVyZWQgY2FsbGJhY2tzLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtvYmplY3R9IHBheWxvYWRcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS5kaXNwYXRjaD1mdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAgICBpbnZhcmlhbnQoXHJcbiAgICAgICF0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmcsXHJcbiAgICAgICdEaXNwYXRjaC5kaXNwYXRjaCguLi4pOiBDYW5ub3QgZGlzcGF0Y2ggaW4gdGhlIG1pZGRsZSBvZiBhIGRpc3BhdGNoLidcclxuICAgICk7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX3N0YXJ0RGlzcGF0Y2hpbmcocGF5bG9hZCk7XHJcbiAgICB0cnkge1xyXG4gICAgICBmb3IgKHZhciBpZCBpbiB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrcykge1xyXG4gICAgICAgIGlmICh0aGlzLiREaXNwYXRjaGVyX2lzUGVuZGluZ1tpZF0pIHtcclxuICAgICAgICAgIGNvbnRpbnVlO1xyXG4gICAgICAgIH1cclxuICAgICAgICB0aGlzLiREaXNwYXRjaGVyX2ludm9rZUNhbGxiYWNrKGlkKTtcclxuICAgICAgfVxyXG4gICAgfSBmaW5hbGx5IHtcclxuICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9zdG9wRGlzcGF0Y2hpbmcoKTtcclxuICAgIH1cclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBJcyB0aGlzIERpc3BhdGNoZXIgY3VycmVudGx5IGRpc3BhdGNoaW5nLlxyXG4gICAqXHJcbiAgICogQHJldHVybiB7Ym9vbGVhbn1cclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS5pc0Rpc3BhdGNoaW5nPWZ1bmN0aW9uKCkge1xyXG4gICAgcmV0dXJuIHRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZztcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBDYWxsIHRoZSBjYWxsYmFjayBzdG9yZWQgd2l0aCB0aGUgZ2l2ZW4gaWQuIEFsc28gZG8gc29tZSBpbnRlcm5hbFxyXG4gICAqIGJvb2trZWVwaW5nLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IGlkXHJcbiAgICogQGludGVybmFsXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUuJERpc3BhdGNoZXJfaW52b2tlQ2FsbGJhY2s9ZnVuY3Rpb24oaWQpIHtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nW2lkXSA9IHRydWU7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0odGhpcy4kRGlzcGF0Y2hlcl9wZW5kaW5nUGF5bG9hZCk7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzSGFuZGxlZFtpZF0gPSB0cnVlO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIFNldCB1cCBib29ra2VlcGluZyBuZWVkZWQgd2hlbiBkaXNwYXRjaGluZy5cclxuICAgKlxyXG4gICAqIEBwYXJhbSB7b2JqZWN0fSBwYXlsb2FkXHJcbiAgICogQGludGVybmFsXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUuJERpc3BhdGNoZXJfc3RhcnREaXNwYXRjaGluZz1mdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAgICBmb3IgKHZhciBpZCBpbiB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrcykge1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2lzUGVuZGluZ1tpZF0gPSBmYWxzZTtcclxuICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0hhbmRsZWRbaWRdID0gZmFsc2U7XHJcbiAgICB9XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX3BlbmRpbmdQYXlsb2FkID0gcGF5bG9hZDtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyA9IHRydWU7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogQ2xlYXIgYm9va2tlZXBpbmcgdXNlZCBmb3IgZGlzcGF0Y2hpbmcuXHJcbiAgICpcclxuICAgKiBAaW50ZXJuYWxcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS4kRGlzcGF0Y2hlcl9zdG9wRGlzcGF0Y2hpbmc9ZnVuY3Rpb24oKSB7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX3BlbmRpbmdQYXlsb2FkID0gbnVsbDtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyA9IGZhbHNlO1xyXG4gIH07XHJcblxyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBEaXNwYXRjaGVyO1xyXG4iLCIvKipcclxuICogQ29weXJpZ2h0IChjKSAyMDE0LCBGYWNlYm9vaywgSW5jLlxyXG4gKiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKlxyXG4gKiBUaGlzIHNvdXJjZSBjb2RlIGlzIGxpY2Vuc2VkIHVuZGVyIHRoZSBCU0Qtc3R5bGUgbGljZW5zZSBmb3VuZCBpbiB0aGVcclxuICogTElDRU5TRSBmaWxlIGluIHRoZSByb290IGRpcmVjdG9yeSBvZiB0aGlzIHNvdXJjZSB0cmVlLiBBbiBhZGRpdGlvbmFsIGdyYW50XHJcbiAqIG9mIHBhdGVudCByaWdodHMgY2FuIGJlIGZvdW5kIGluIHRoZSBQQVRFTlRTIGZpbGUgaW4gdGhlIHNhbWUgZGlyZWN0b3J5LlxyXG4gKlxyXG4gKiBAcHJvdmlkZXNNb2R1bGUgaW52YXJpYW50XHJcbiAqL1xyXG5cclxuXCJ1c2Ugc3RyaWN0XCI7XHJcblxyXG4vKipcclxuICogVXNlIGludmFyaWFudCgpIHRvIGFzc2VydCBzdGF0ZSB3aGljaCB5b3VyIHByb2dyYW0gYXNzdW1lcyB0byBiZSB0cnVlLlxyXG4gKlxyXG4gKiBQcm92aWRlIHNwcmludGYtc3R5bGUgZm9ybWF0IChvbmx5ICVzIGlzIHN1cHBvcnRlZCkgYW5kIGFyZ3VtZW50c1xyXG4gKiB0byBwcm92aWRlIGluZm9ybWF0aW9uIGFib3V0IHdoYXQgYnJva2UgYW5kIHdoYXQgeW91IHdlcmVcclxuICogZXhwZWN0aW5nLlxyXG4gKlxyXG4gKiBUaGUgaW52YXJpYW50IG1lc3NhZ2Ugd2lsbCBiZSBzdHJpcHBlZCBpbiBwcm9kdWN0aW9uLCBidXQgdGhlIGludmFyaWFudFxyXG4gKiB3aWxsIHJlbWFpbiB0byBlbnN1cmUgbG9naWMgZG9lcyBub3QgZGlmZmVyIGluIHByb2R1Y3Rpb24uXHJcbiAqL1xyXG5cclxudmFyIGludmFyaWFudCA9IGZ1bmN0aW9uKGNvbmRpdGlvbiwgZm9ybWF0LCBhLCBiLCBjLCBkLCBlLCBmKSB7XHJcbiAgaWYgKGZhbHNlKSB7XHJcbiAgICBpZiAoZm9ybWF0ID09PSB1bmRlZmluZWQpIHtcclxuICAgICAgdGhyb3cgbmV3IEVycm9yKCdpbnZhcmlhbnQgcmVxdWlyZXMgYW4gZXJyb3IgbWVzc2FnZSBhcmd1bWVudCcpO1xyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgaWYgKCFjb25kaXRpb24pIHtcclxuICAgIHZhciBlcnJvcjtcclxuICAgIGlmIChmb3JtYXQgPT09IHVuZGVmaW5lZCkge1xyXG4gICAgICBlcnJvciA9IG5ldyBFcnJvcihcclxuICAgICAgICAnTWluaWZpZWQgZXhjZXB0aW9uIG9jY3VycmVkOyB1c2UgdGhlIG5vbi1taW5pZmllZCBkZXYgZW52aXJvbm1lbnQgJyArXHJcbiAgICAgICAgJ2ZvciB0aGUgZnVsbCBlcnJvciBtZXNzYWdlIGFuZCBhZGRpdGlvbmFsIGhlbHBmdWwgd2FybmluZ3MuJ1xyXG4gICAgICApO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgdmFyIGFyZ3MgPSBbYSwgYiwgYywgZCwgZSwgZl07XHJcbiAgICAgIHZhciBhcmdJbmRleCA9IDA7XHJcbiAgICAgIGVycm9yID0gbmV3IEVycm9yKFxyXG4gICAgICAgICdJbnZhcmlhbnQgVmlvbGF0aW9uOiAnICtcclxuICAgICAgICBmb3JtYXQucmVwbGFjZSgvJXMvZywgZnVuY3Rpb24oKSB7IHJldHVybiBhcmdzW2FyZ0luZGV4KytdOyB9KVxyXG4gICAgICApO1xyXG4gICAgfVxyXG5cclxuICAgIGVycm9yLmZyYW1lc1RvUG9wID0gMTsgLy8gd2UgZG9uJ3QgY2FyZSBhYm91dCBpbnZhcmlhbnQncyBvd24gZnJhbWVcclxuICAgIHRocm93IGVycm9yO1xyXG4gIH1cclxufTtcclxuXHJcbm1vZHVsZS5leHBvcnRzID0gaW52YXJpYW50O1xyXG4iLCIvKipcclxuICogQ29weXJpZ2h0IDIwMTMtMjAxNCBGYWNlYm9vaywgSW5jLlxyXG4gKlxyXG4gKiBMaWNlbnNlZCB1bmRlciB0aGUgQXBhY2hlIExpY2Vuc2UsIFZlcnNpb24gMi4wICh0aGUgXCJMaWNlbnNlXCIpO1xyXG4gKiB5b3UgbWF5IG5vdCB1c2UgdGhpcyBmaWxlIGV4Y2VwdCBpbiBjb21wbGlhbmNlIHdpdGggdGhlIExpY2Vuc2UuXHJcbiAqIFlvdSBtYXkgb2J0YWluIGEgY29weSBvZiB0aGUgTGljZW5zZSBhdFxyXG4gKlxyXG4gKiBodHRwOi8vd3d3LmFwYWNoZS5vcmcvbGljZW5zZXMvTElDRU5TRS0yLjBcclxuICpcclxuICogVW5sZXNzIHJlcXVpcmVkIGJ5IGFwcGxpY2FibGUgbGF3IG9yIGFncmVlZCB0byBpbiB3cml0aW5nLCBzb2Z0d2FyZVxyXG4gKiBkaXN0cmlidXRlZCB1bmRlciB0aGUgTGljZW5zZSBpcyBkaXN0cmlidXRlZCBvbiBhbiBcIkFTIElTXCIgQkFTSVMsXHJcbiAqIFdJVEhPVVQgV0FSUkFOVElFUyBPUiBDT05ESVRJT05TIE9GIEFOWSBLSU5ELCBlaXRoZXIgZXhwcmVzcyBvciBpbXBsaWVkLlxyXG4gKiBTZWUgdGhlIExpY2Vuc2UgZm9yIHRoZSBzcGVjaWZpYyBsYW5ndWFnZSBnb3Zlcm5pbmcgcGVybWlzc2lvbnMgYW5kXHJcbiAqIGxpbWl0YXRpb25zIHVuZGVyIHRoZSBMaWNlbnNlLlxyXG4gKlxyXG4gKi9cclxuXHJcblwidXNlIHN0cmljdFwiO1xyXG5cclxuLyoqXHJcbiAqIENvbnN0cnVjdHMgYW4gZW51bWVyYXRpb24gd2l0aCBrZXlzIGVxdWFsIHRvIHRoZWlyIHZhbHVlLlxyXG4gKlxyXG4gKiBGb3IgZXhhbXBsZTpcclxuICpcclxuICogICB2YXIgQ09MT1JTID0ga2V5TWlycm9yKHtibHVlOiBudWxsLCByZWQ6IG51bGx9KTtcclxuICogICB2YXIgbXlDb2xvciA9IENPTE9SUy5ibHVlO1xyXG4gKiAgIHZhciBpc0NvbG9yVmFsaWQgPSAhIUNPTE9SU1tteUNvbG9yXTtcclxuICpcclxuICogVGhlIGxhc3QgbGluZSBjb3VsZCBub3QgYmUgcGVyZm9ybWVkIGlmIHRoZSB2YWx1ZXMgb2YgdGhlIGdlbmVyYXRlZCBlbnVtIHdlcmVcclxuICogbm90IGVxdWFsIHRvIHRoZWlyIGtleXMuXHJcbiAqXHJcbiAqICAgSW5wdXQ6ICB7a2V5MTogdmFsMSwga2V5MjogdmFsMn1cclxuICogICBPdXRwdXQ6IHtrZXkxOiBrZXkxLCBrZXkyOiBrZXkyfVxyXG4gKlxyXG4gKiBAcGFyYW0ge29iamVjdH0gb2JqXHJcbiAqIEByZXR1cm4ge29iamVjdH1cclxuICovXHJcbnZhciBrZXlNaXJyb3IgPSBmdW5jdGlvbihvYmopIHtcclxuICB2YXIgcmV0ID0ge307XHJcbiAgdmFyIGtleTtcclxuICBpZiAoIShvYmogaW5zdGFuY2VvZiBPYmplY3QgJiYgIUFycmF5LmlzQXJyYXkob2JqKSkpIHtcclxuICAgIHRocm93IG5ldyBFcnJvcigna2V5TWlycm9yKC4uLik6IEFyZ3VtZW50IG11c3QgYmUgYW4gb2JqZWN0LicpO1xyXG4gIH1cclxuICBmb3IgKGtleSBpbiBvYmopIHtcclxuICAgIGlmICghb2JqLmhhc093blByb3BlcnR5KGtleSkpIHtcclxuICAgICAgY29udGludWU7XHJcbiAgICB9XHJcbiAgICByZXRba2V5XSA9IGtleTtcclxuICB9XHJcbiAgcmV0dXJuIHJldDtcclxufTtcclxuXHJcbm1vZHVsZS5leHBvcnRzID0ga2V5TWlycm9yO1xyXG4iLCIndXNlIHN0cmljdCc7XHJcblxyXG5mdW5jdGlvbiBUb09iamVjdCh2YWwpIHtcclxuXHRpZiAodmFsID09IG51bGwpIHtcclxuXHRcdHRocm93IG5ldyBUeXBlRXJyb3IoJ09iamVjdC5hc3NpZ24gY2Fubm90IGJlIGNhbGxlZCB3aXRoIG51bGwgb3IgdW5kZWZpbmVkJyk7XHJcblx0fVxyXG5cclxuXHRyZXR1cm4gT2JqZWN0KHZhbCk7XHJcbn1cclxuXHJcbm1vZHVsZS5leHBvcnRzID0gT2JqZWN0LmFzc2lnbiB8fCBmdW5jdGlvbiAodGFyZ2V0LCBzb3VyY2UpIHtcclxuXHR2YXIgcGVuZGluZ0V4Y2VwdGlvbjtcclxuXHR2YXIgZnJvbTtcclxuXHR2YXIga2V5cztcclxuXHR2YXIgdG8gPSBUb09iamVjdCh0YXJnZXQpO1xyXG5cclxuXHRmb3IgKHZhciBzID0gMTsgcyA8IGFyZ3VtZW50cy5sZW5ndGg7IHMrKykge1xyXG5cdFx0ZnJvbSA9IGFyZ3VtZW50c1tzXTtcclxuXHRcdGtleXMgPSBPYmplY3Qua2V5cyhPYmplY3QoZnJvbSkpO1xyXG5cclxuXHRcdGZvciAodmFyIGkgPSAwOyBpIDwga2V5cy5sZW5ndGg7IGkrKykge1xyXG5cdFx0XHR0cnkge1xyXG5cdFx0XHRcdHRvW2tleXNbaV1dID0gZnJvbVtrZXlzW2ldXTtcclxuXHRcdFx0fSBjYXRjaCAoZXJyKSB7XHJcblx0XHRcdFx0aWYgKHBlbmRpbmdFeGNlcHRpb24gPT09IHVuZGVmaW5lZCkge1xyXG5cdFx0XHRcdFx0cGVuZGluZ0V4Y2VwdGlvbiA9IGVycjtcclxuXHRcdFx0XHR9XHJcblx0XHRcdH1cclxuXHRcdH1cclxuXHR9XHJcblxyXG5cdGlmIChwZW5kaW5nRXhjZXB0aW9uKSB7XHJcblx0XHR0aHJvdyBwZW5kaW5nRXhjZXB0aW9uO1xyXG5cdH1cclxuXHJcblx0cmV0dXJuIHRvO1xyXG59O1xyXG4iLCIvLyBDb3B5cmlnaHQgSm95ZW50LCBJbmMuIGFuZCBvdGhlciBOb2RlIGNvbnRyaWJ1dG9ycy5cclxuLy9cclxuLy8gUGVybWlzc2lvbiBpcyBoZXJlYnkgZ3JhbnRlZCwgZnJlZSBvZiBjaGFyZ2UsIHRvIGFueSBwZXJzb24gb2J0YWluaW5nIGFcclxuLy8gY29weSBvZiB0aGlzIHNvZnR3YXJlIGFuZCBhc3NvY2lhdGVkIGRvY3VtZW50YXRpb24gZmlsZXMgKHRoZVxyXG4vLyBcIlNvZnR3YXJlXCIpLCB0byBkZWFsIGluIHRoZSBTb2Z0d2FyZSB3aXRob3V0IHJlc3RyaWN0aW9uLCBpbmNsdWRpbmdcclxuLy8gd2l0aG91dCBsaW1pdGF0aW9uIHRoZSByaWdodHMgdG8gdXNlLCBjb3B5LCBtb2RpZnksIG1lcmdlLCBwdWJsaXNoLFxyXG4vLyBkaXN0cmlidXRlLCBzdWJsaWNlbnNlLCBhbmQvb3Igc2VsbCBjb3BpZXMgb2YgdGhlIFNvZnR3YXJlLCBhbmQgdG8gcGVybWl0XHJcbi8vIHBlcnNvbnMgdG8gd2hvbSB0aGUgU29mdHdhcmUgaXMgZnVybmlzaGVkIHRvIGRvIHNvLCBzdWJqZWN0IHRvIHRoZVxyXG4vLyBmb2xsb3dpbmcgY29uZGl0aW9uczpcclxuLy9cclxuLy8gVGhlIGFib3ZlIGNvcHlyaWdodCBub3RpY2UgYW5kIHRoaXMgcGVybWlzc2lvbiBub3RpY2Ugc2hhbGwgYmUgaW5jbHVkZWRcclxuLy8gaW4gYWxsIGNvcGllcyBvciBzdWJzdGFudGlhbCBwb3J0aW9ucyBvZiB0aGUgU29mdHdhcmUuXHJcbi8vXHJcbi8vIFRIRSBTT0ZUV0FSRSBJUyBQUk9WSURFRCBcIkFTIElTXCIsIFdJVEhPVVQgV0FSUkFOVFkgT0YgQU5ZIEtJTkQsIEVYUFJFU1NcclxuLy8gT1IgSU1QTElFRCwgSU5DTFVESU5HIEJVVCBOT1QgTElNSVRFRCBUTyBUSEUgV0FSUkFOVElFUyBPRlxyXG4vLyBNRVJDSEFOVEFCSUxJVFksIEZJVE5FU1MgRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFIEFORCBOT05JTkZSSU5HRU1FTlQuIElOXHJcbi8vIE5PIEVWRU5UIFNIQUxMIFRIRSBBVVRIT1JTIE9SIENPUFlSSUdIVCBIT0xERVJTIEJFIExJQUJMRSBGT1IgQU5ZIENMQUlNLFxyXG4vLyBEQU1BR0VTIE9SIE9USEVSIExJQUJJTElUWSwgV0hFVEhFUiBJTiBBTiBBQ1RJT04gT0YgQ09OVFJBQ1QsIFRPUlQgT1JcclxuLy8gT1RIRVJXSVNFLCBBUklTSU5HIEZST00sIE9VVCBPRiBPUiBJTiBDT05ORUNUSU9OIFdJVEggVEhFIFNPRlRXQVJFIE9SIFRIRVxyXG4vLyBVU0UgT1IgT1RIRVIgREVBTElOR1MgSU4gVEhFIFNPRlRXQVJFLlxyXG5cclxuZnVuY3Rpb24gRXZlbnRFbWl0dGVyKCkge1xyXG4gIHRoaXMuX2V2ZW50cyA9IHRoaXMuX2V2ZW50cyB8fCB7fTtcclxuICB0aGlzLl9tYXhMaXN0ZW5lcnMgPSB0aGlzLl9tYXhMaXN0ZW5lcnMgfHwgdW5kZWZpbmVkO1xyXG59XHJcbm1vZHVsZS5leHBvcnRzID0gRXZlbnRFbWl0dGVyO1xyXG5cclxuLy8gQmFja3dhcmRzLWNvbXBhdCB3aXRoIG5vZGUgMC4xMC54XHJcbkV2ZW50RW1pdHRlci5FdmVudEVtaXR0ZXIgPSBFdmVudEVtaXR0ZXI7XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLl9ldmVudHMgPSB1bmRlZmluZWQ7XHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuX21heExpc3RlbmVycyA9IHVuZGVmaW5lZDtcclxuXHJcbi8vIEJ5IGRlZmF1bHQgRXZlbnRFbWl0dGVycyB3aWxsIHByaW50IGEgd2FybmluZyBpZiBtb3JlIHRoYW4gMTAgbGlzdGVuZXJzIGFyZVxyXG4vLyBhZGRlZCB0byBpdC4gVGhpcyBpcyBhIHVzZWZ1bCBkZWZhdWx0IHdoaWNoIGhlbHBzIGZpbmRpbmcgbWVtb3J5IGxlYWtzLlxyXG5FdmVudEVtaXR0ZXIuZGVmYXVsdE1heExpc3RlbmVycyA9IDEwO1xyXG5cclxuLy8gT2J2aW91c2x5IG5vdCBhbGwgRW1pdHRlcnMgc2hvdWxkIGJlIGxpbWl0ZWQgdG8gMTAuIFRoaXMgZnVuY3Rpb24gYWxsb3dzXHJcbi8vIHRoYXQgdG8gYmUgaW5jcmVhc2VkLiBTZXQgdG8gemVybyBmb3IgdW5saW1pdGVkLlxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLnNldE1heExpc3RlbmVycyA9IGZ1bmN0aW9uKG4pIHtcclxuICBpZiAoIWlzTnVtYmVyKG4pIHx8IG4gPCAwIHx8IGlzTmFOKG4pKVxyXG4gICAgdGhyb3cgVHlwZUVycm9yKCduIG11c3QgYmUgYSBwb3NpdGl2ZSBudW1iZXInKTtcclxuICB0aGlzLl9tYXhMaXN0ZW5lcnMgPSBuO1xyXG4gIHJldHVybiB0aGlzO1xyXG59O1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5lbWl0ID0gZnVuY3Rpb24odHlwZSkge1xyXG4gIHZhciBlciwgaGFuZGxlciwgbGVuLCBhcmdzLCBpLCBsaXN0ZW5lcnM7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzKVxyXG4gICAgdGhpcy5fZXZlbnRzID0ge307XHJcblxyXG4gIC8vIElmIHRoZXJlIGlzIG5vICdlcnJvcicgZXZlbnQgbGlzdGVuZXIgdGhlbiB0aHJvdy5cclxuICBpZiAodHlwZSA9PT0gJ2Vycm9yJykge1xyXG4gICAgaWYgKCF0aGlzLl9ldmVudHMuZXJyb3IgfHxcclxuICAgICAgICAoaXNPYmplY3QodGhpcy5fZXZlbnRzLmVycm9yKSAmJiAhdGhpcy5fZXZlbnRzLmVycm9yLmxlbmd0aCkpIHtcclxuICAgICAgZXIgPSBhcmd1bWVudHNbMV07XHJcbiAgICAgIGlmIChlciBpbnN0YW5jZW9mIEVycm9yKSB7XHJcbiAgICAgICAgdGhyb3cgZXI7IC8vIFVuaGFuZGxlZCAnZXJyb3InIGV2ZW50XHJcbiAgICAgIH1cclxuICAgICAgdGhyb3cgVHlwZUVycm9yKCdVbmNhdWdodCwgdW5zcGVjaWZpZWQgXCJlcnJvclwiIGV2ZW50LicpO1xyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgaGFuZGxlciA9IHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuXHJcbiAgaWYgKGlzVW5kZWZpbmVkKGhhbmRsZXIpKVxyXG4gICAgcmV0dXJuIGZhbHNlO1xyXG5cclxuICBpZiAoaXNGdW5jdGlvbihoYW5kbGVyKSkge1xyXG4gICAgc3dpdGNoIChhcmd1bWVudHMubGVuZ3RoKSB7XHJcbiAgICAgIC8vIGZhc3QgY2FzZXNcclxuICAgICAgY2FzZSAxOlxyXG4gICAgICAgIGhhbmRsZXIuY2FsbCh0aGlzKTtcclxuICAgICAgICBicmVhaztcclxuICAgICAgY2FzZSAyOlxyXG4gICAgICAgIGhhbmRsZXIuY2FsbCh0aGlzLCBhcmd1bWVudHNbMV0pO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICBjYXNlIDM6XHJcbiAgICAgICAgaGFuZGxlci5jYWxsKHRoaXMsIGFyZ3VtZW50c1sxXSwgYXJndW1lbnRzWzJdKTtcclxuICAgICAgICBicmVhaztcclxuICAgICAgLy8gc2xvd2VyXHJcbiAgICAgIGRlZmF1bHQ6XHJcbiAgICAgICAgbGVuID0gYXJndW1lbnRzLmxlbmd0aDtcclxuICAgICAgICBhcmdzID0gbmV3IEFycmF5KGxlbiAtIDEpO1xyXG4gICAgICAgIGZvciAoaSA9IDE7IGkgPCBsZW47IGkrKylcclxuICAgICAgICAgIGFyZ3NbaSAtIDFdID0gYXJndW1lbnRzW2ldO1xyXG4gICAgICAgIGhhbmRsZXIuYXBwbHkodGhpcywgYXJncyk7XHJcbiAgICB9XHJcbiAgfSBlbHNlIGlmIChpc09iamVjdChoYW5kbGVyKSkge1xyXG4gICAgbGVuID0gYXJndW1lbnRzLmxlbmd0aDtcclxuICAgIGFyZ3MgPSBuZXcgQXJyYXkobGVuIC0gMSk7XHJcbiAgICBmb3IgKGkgPSAxOyBpIDwgbGVuOyBpKyspXHJcbiAgICAgIGFyZ3NbaSAtIDFdID0gYXJndW1lbnRzW2ldO1xyXG5cclxuICAgIGxpc3RlbmVycyA9IGhhbmRsZXIuc2xpY2UoKTtcclxuICAgIGxlbiA9IGxpc3RlbmVycy5sZW5ndGg7XHJcbiAgICBmb3IgKGkgPSAwOyBpIDwgbGVuOyBpKyspXHJcbiAgICAgIGxpc3RlbmVyc1tpXS5hcHBseSh0aGlzLCBhcmdzKTtcclxuICB9XHJcblxyXG4gIHJldHVybiB0cnVlO1xyXG59O1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5hZGRMaXN0ZW5lciA9IGZ1bmN0aW9uKHR5cGUsIGxpc3RlbmVyKSB7XHJcbiAgdmFyIG07XHJcblxyXG4gIGlmICghaXNGdW5jdGlvbihsaXN0ZW5lcikpXHJcbiAgICB0aHJvdyBUeXBlRXJyb3IoJ2xpc3RlbmVyIG11c3QgYmUgYSBmdW5jdGlvbicpO1xyXG5cclxuICBpZiAoIXRoaXMuX2V2ZW50cylcclxuICAgIHRoaXMuX2V2ZW50cyA9IHt9O1xyXG5cclxuICAvLyBUbyBhdm9pZCByZWN1cnNpb24gaW4gdGhlIGNhc2UgdGhhdCB0eXBlID09PSBcIm5ld0xpc3RlbmVyXCIhIEJlZm9yZVxyXG4gIC8vIGFkZGluZyBpdCB0byB0aGUgbGlzdGVuZXJzLCBmaXJzdCBlbWl0IFwibmV3TGlzdGVuZXJcIi5cclxuICBpZiAodGhpcy5fZXZlbnRzLm5ld0xpc3RlbmVyKVxyXG4gICAgdGhpcy5lbWl0KCduZXdMaXN0ZW5lcicsIHR5cGUsXHJcbiAgICAgICAgICAgICAgaXNGdW5jdGlvbihsaXN0ZW5lci5saXN0ZW5lcikgP1xyXG4gICAgICAgICAgICAgIGxpc3RlbmVyLmxpc3RlbmVyIDogbGlzdGVuZXIpO1xyXG5cclxuICBpZiAoIXRoaXMuX2V2ZW50c1t0eXBlXSlcclxuICAgIC8vIE9wdGltaXplIHRoZSBjYXNlIG9mIG9uZSBsaXN0ZW5lci4gRG9uJ3QgbmVlZCB0aGUgZXh0cmEgYXJyYXkgb2JqZWN0LlxyXG4gICAgdGhpcy5fZXZlbnRzW3R5cGVdID0gbGlzdGVuZXI7XHJcbiAgZWxzZSBpZiAoaXNPYmplY3QodGhpcy5fZXZlbnRzW3R5cGVdKSlcclxuICAgIC8vIElmIHdlJ3ZlIGFscmVhZHkgZ290IGFuIGFycmF5LCBqdXN0IGFwcGVuZC5cclxuICAgIHRoaXMuX2V2ZW50c1t0eXBlXS5wdXNoKGxpc3RlbmVyKTtcclxuICBlbHNlXHJcbiAgICAvLyBBZGRpbmcgdGhlIHNlY29uZCBlbGVtZW50LCBuZWVkIHRvIGNoYW5nZSB0byBhcnJheS5cclxuICAgIHRoaXMuX2V2ZW50c1t0eXBlXSA9IFt0aGlzLl9ldmVudHNbdHlwZV0sIGxpc3RlbmVyXTtcclxuXHJcbiAgLy8gQ2hlY2sgZm9yIGxpc3RlbmVyIGxlYWtcclxuICBpZiAoaXNPYmplY3QodGhpcy5fZXZlbnRzW3R5cGVdKSAmJiAhdGhpcy5fZXZlbnRzW3R5cGVdLndhcm5lZCkge1xyXG4gICAgdmFyIG07XHJcbiAgICBpZiAoIWlzVW5kZWZpbmVkKHRoaXMuX21heExpc3RlbmVycykpIHtcclxuICAgICAgbSA9IHRoaXMuX21heExpc3RlbmVycztcclxuICAgIH0gZWxzZSB7XHJcbiAgICAgIG0gPSBFdmVudEVtaXR0ZXIuZGVmYXVsdE1heExpc3RlbmVycztcclxuICAgIH1cclxuXHJcbiAgICBpZiAobSAmJiBtID4gMCAmJiB0aGlzLl9ldmVudHNbdHlwZV0ubGVuZ3RoID4gbSkge1xyXG4gICAgICB0aGlzLl9ldmVudHNbdHlwZV0ud2FybmVkID0gdHJ1ZTtcclxuICAgICAgY29uc29sZS5lcnJvcignKG5vZGUpIHdhcm5pbmc6IHBvc3NpYmxlIEV2ZW50RW1pdHRlciBtZW1vcnkgJyArXHJcbiAgICAgICAgICAgICAgICAgICAgJ2xlYWsgZGV0ZWN0ZWQuICVkIGxpc3RlbmVycyBhZGRlZC4gJyArXHJcbiAgICAgICAgICAgICAgICAgICAgJ1VzZSBlbWl0dGVyLnNldE1heExpc3RlbmVycygpIHRvIGluY3JlYXNlIGxpbWl0LicsXHJcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5fZXZlbnRzW3R5cGVdLmxlbmd0aCk7XHJcbiAgICAgIGlmICh0eXBlb2YgY29uc29sZS50cmFjZSA9PT0gJ2Z1bmN0aW9uJykge1xyXG4gICAgICAgIC8vIG5vdCBzdXBwb3J0ZWQgaW4gSUUgMTBcclxuICAgICAgICBjb25zb2xlLnRyYWNlKCk7XHJcbiAgICAgIH1cclxuICAgIH1cclxuICB9XHJcblxyXG4gIHJldHVybiB0aGlzO1xyXG59O1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5vbiA9IEV2ZW50RW1pdHRlci5wcm90b3R5cGUuYWRkTGlzdGVuZXI7XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLm9uY2UgPSBmdW5jdGlvbih0eXBlLCBsaXN0ZW5lcikge1xyXG4gIGlmICghaXNGdW5jdGlvbihsaXN0ZW5lcikpXHJcbiAgICB0aHJvdyBUeXBlRXJyb3IoJ2xpc3RlbmVyIG11c3QgYmUgYSBmdW5jdGlvbicpO1xyXG5cclxuICB2YXIgZmlyZWQgPSBmYWxzZTtcclxuXHJcbiAgZnVuY3Rpb24gZygpIHtcclxuICAgIHRoaXMucmVtb3ZlTGlzdGVuZXIodHlwZSwgZyk7XHJcblxyXG4gICAgaWYgKCFmaXJlZCkge1xyXG4gICAgICBmaXJlZCA9IHRydWU7XHJcbiAgICAgIGxpc3RlbmVyLmFwcGx5KHRoaXMsIGFyZ3VtZW50cyk7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICBnLmxpc3RlbmVyID0gbGlzdGVuZXI7XHJcbiAgdGhpcy5vbih0eXBlLCBnKTtcclxuXHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG4vLyBlbWl0cyBhICdyZW1vdmVMaXN0ZW5lcicgZXZlbnQgaWZmIHRoZSBsaXN0ZW5lciB3YXMgcmVtb3ZlZFxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLnJlbW92ZUxpc3RlbmVyID0gZnVuY3Rpb24odHlwZSwgbGlzdGVuZXIpIHtcclxuICB2YXIgbGlzdCwgcG9zaXRpb24sIGxlbmd0aCwgaTtcclxuXHJcbiAgaWYgKCFpc0Z1bmN0aW9uKGxpc3RlbmVyKSlcclxuICAgIHRocm93IFR5cGVFcnJvcignbGlzdGVuZXIgbXVzdCBiZSBhIGZ1bmN0aW9uJyk7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzIHx8ICF0aGlzLl9ldmVudHNbdHlwZV0pXHJcbiAgICByZXR1cm4gdGhpcztcclxuXHJcbiAgbGlzdCA9IHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuICBsZW5ndGggPSBsaXN0Lmxlbmd0aDtcclxuICBwb3NpdGlvbiA9IC0xO1xyXG5cclxuICBpZiAobGlzdCA9PT0gbGlzdGVuZXIgfHxcclxuICAgICAgKGlzRnVuY3Rpb24obGlzdC5saXN0ZW5lcikgJiYgbGlzdC5saXN0ZW5lciA9PT0gbGlzdGVuZXIpKSB7XHJcbiAgICBkZWxldGUgdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG4gICAgaWYgKHRoaXMuX2V2ZW50cy5yZW1vdmVMaXN0ZW5lcilcclxuICAgICAgdGhpcy5lbWl0KCdyZW1vdmVMaXN0ZW5lcicsIHR5cGUsIGxpc3RlbmVyKTtcclxuXHJcbiAgfSBlbHNlIGlmIChpc09iamVjdChsaXN0KSkge1xyXG4gICAgZm9yIChpID0gbGVuZ3RoOyBpLS0gPiAwOykge1xyXG4gICAgICBpZiAobGlzdFtpXSA9PT0gbGlzdGVuZXIgfHxcclxuICAgICAgICAgIChsaXN0W2ldLmxpc3RlbmVyICYmIGxpc3RbaV0ubGlzdGVuZXIgPT09IGxpc3RlbmVyKSkge1xyXG4gICAgICAgIHBvc2l0aW9uID0gaTtcclxuICAgICAgICBicmVhaztcclxuICAgICAgfVxyXG4gICAgfVxyXG5cclxuICAgIGlmIChwb3NpdGlvbiA8IDApXHJcbiAgICAgIHJldHVybiB0aGlzO1xyXG5cclxuICAgIGlmIChsaXN0Lmxlbmd0aCA9PT0gMSkge1xyXG4gICAgICBsaXN0Lmxlbmd0aCA9IDA7XHJcbiAgICAgIGRlbGV0ZSB0aGlzLl9ldmVudHNbdHlwZV07XHJcbiAgICB9IGVsc2Uge1xyXG4gICAgICBsaXN0LnNwbGljZShwb3NpdGlvbiwgMSk7XHJcbiAgICB9XHJcblxyXG4gICAgaWYgKHRoaXMuX2V2ZW50cy5yZW1vdmVMaXN0ZW5lcilcclxuICAgICAgdGhpcy5lbWl0KCdyZW1vdmVMaXN0ZW5lcicsIHR5cGUsIGxpc3RlbmVyKTtcclxuICB9XHJcblxyXG4gIHJldHVybiB0aGlzO1xyXG59O1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5yZW1vdmVBbGxMaXN0ZW5lcnMgPSBmdW5jdGlvbih0eXBlKSB7XHJcbiAgdmFyIGtleSwgbGlzdGVuZXJzO1xyXG5cclxuICBpZiAoIXRoaXMuX2V2ZW50cylcclxuICAgIHJldHVybiB0aGlzO1xyXG5cclxuICAvLyBub3QgbGlzdGVuaW5nIGZvciByZW1vdmVMaXN0ZW5lciwgbm8gbmVlZCB0byBlbWl0XHJcbiAgaWYgKCF0aGlzLl9ldmVudHMucmVtb3ZlTGlzdGVuZXIpIHtcclxuICAgIGlmIChhcmd1bWVudHMubGVuZ3RoID09PSAwKVxyXG4gICAgICB0aGlzLl9ldmVudHMgPSB7fTtcclxuICAgIGVsc2UgaWYgKHRoaXMuX2V2ZW50c1t0eXBlXSlcclxuICAgICAgZGVsZXRlIHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuICAgIHJldHVybiB0aGlzO1xyXG4gIH1cclxuXHJcbiAgLy8gZW1pdCByZW1vdmVMaXN0ZW5lciBmb3IgYWxsIGxpc3RlbmVycyBvbiBhbGwgZXZlbnRzXHJcbiAgaWYgKGFyZ3VtZW50cy5sZW5ndGggPT09IDApIHtcclxuICAgIGZvciAoa2V5IGluIHRoaXMuX2V2ZW50cykge1xyXG4gICAgICBpZiAoa2V5ID09PSAncmVtb3ZlTGlzdGVuZXInKSBjb250aW51ZTtcclxuICAgICAgdGhpcy5yZW1vdmVBbGxMaXN0ZW5lcnMoa2V5KTtcclxuICAgIH1cclxuICAgIHRoaXMucmVtb3ZlQWxsTGlzdGVuZXJzKCdyZW1vdmVMaXN0ZW5lcicpO1xyXG4gICAgdGhpcy5fZXZlbnRzID0ge307XHJcbiAgICByZXR1cm4gdGhpcztcclxuICB9XHJcblxyXG4gIGxpc3RlbmVycyA9IHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuXHJcbiAgaWYgKGlzRnVuY3Rpb24obGlzdGVuZXJzKSkge1xyXG4gICAgdGhpcy5yZW1vdmVMaXN0ZW5lcih0eXBlLCBsaXN0ZW5lcnMpO1xyXG4gIH0gZWxzZSB7XHJcbiAgICAvLyBMSUZPIG9yZGVyXHJcbiAgICB3aGlsZSAobGlzdGVuZXJzLmxlbmd0aClcclxuICAgICAgdGhpcy5yZW1vdmVMaXN0ZW5lcih0eXBlLCBsaXN0ZW5lcnNbbGlzdGVuZXJzLmxlbmd0aCAtIDFdKTtcclxuICB9XHJcbiAgZGVsZXRlIHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuXHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLmxpc3RlbmVycyA9IGZ1bmN0aW9uKHR5cGUpIHtcclxuICB2YXIgcmV0O1xyXG4gIGlmICghdGhpcy5fZXZlbnRzIHx8ICF0aGlzLl9ldmVudHNbdHlwZV0pXHJcbiAgICByZXQgPSBbXTtcclxuICBlbHNlIGlmIChpc0Z1bmN0aW9uKHRoaXMuX2V2ZW50c1t0eXBlXSkpXHJcbiAgICByZXQgPSBbdGhpcy5fZXZlbnRzW3R5cGVdXTtcclxuICBlbHNlXHJcbiAgICByZXQgPSB0aGlzLl9ldmVudHNbdHlwZV0uc2xpY2UoKTtcclxuICByZXR1cm4gcmV0O1xyXG59O1xyXG5cclxuRXZlbnRFbWl0dGVyLmxpc3RlbmVyQ291bnQgPSBmdW5jdGlvbihlbWl0dGVyLCB0eXBlKSB7XHJcbiAgdmFyIHJldDtcclxuICBpZiAoIWVtaXR0ZXIuX2V2ZW50cyB8fCAhZW1pdHRlci5fZXZlbnRzW3R5cGVdKVxyXG4gICAgcmV0ID0gMDtcclxuICBlbHNlIGlmIChpc0Z1bmN0aW9uKGVtaXR0ZXIuX2V2ZW50c1t0eXBlXSkpXHJcbiAgICByZXQgPSAxO1xyXG4gIGVsc2VcclxuICAgIHJldCA9IGVtaXR0ZXIuX2V2ZW50c1t0eXBlXS5sZW5ndGg7XHJcbiAgcmV0dXJuIHJldDtcclxufTtcclxuXHJcbmZ1bmN0aW9uIGlzRnVuY3Rpb24oYXJnKSB7XHJcbiAgcmV0dXJuIHR5cGVvZiBhcmcgPT09ICdmdW5jdGlvbic7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIGlzTnVtYmVyKGFyZykge1xyXG4gIHJldHVybiB0eXBlb2YgYXJnID09PSAnbnVtYmVyJztcclxufVxyXG5cclxuZnVuY3Rpb24gaXNPYmplY3QoYXJnKSB7XHJcbiAgcmV0dXJuIHR5cGVvZiBhcmcgPT09ICdvYmplY3QnICYmIGFyZyAhPT0gbnVsbDtcclxufVxyXG5cclxuZnVuY3Rpb24gaXNVbmRlZmluZWQoYXJnKSB7XHJcbiAgcmV0dXJuIGFyZyA9PT0gdm9pZCAwO1xyXG59XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5cclxuLyoqKioqKioqKioqICBaw4FWSVNMT1NUSSAgKioqKioqKioqKiovXHJcbnZhciBQcm9maWxlUGhvdG8gPSByZXF1aXJlKCcuLi9jb21wb25lbnRzL3Byb2ZpbGUnKS5Qcm9maWxlUGhvdG87XHJcbnZhciBNZXNzYWdlQ29uc3RhbnRzID0gcmVxdWlyZSgnLi4vZmx1eC9jb25zdGFudHMvQ2hhdENvbnN0YW50cycpLk1lc3NhZ2VDb25zdGFudHM7XHJcbnZhciBNZXNzYWdlQWN0aW9ucyA9IHJlcXVpcmUoJy4uL2ZsdXgvYWN0aW9ucy9jaGF0L01lc3NhZ2VBY3Rpb25DcmVhdG9ycycpO1xyXG52YXIgTWVzc2FnZVN0b3JlID0gcmVxdWlyZSgnLi4vZmx1eC9zdG9yZXMvY2hhdC9NZXNzYWdlU3RvcmUnKTtcclxudmFyIFRpbWVyRmFjdG9yeSA9IHJlcXVpcmUoJy4uL2NvbXBvbmVudHMvdGltZXInKTsvKiBqZSB2IGNhY2hpLCBuZWJ1ZGUgc2Ugdnl0dsOhxZlldCB2w61jZWtyw6F0ICovXHJcblxyXG4vKioqKioqKioqKiogIE5BU1RBVkVOw40gICoqKioqKioqKioqL1xyXG5cclxuLyoqIE9ka2F6eSBrZSBrb211bmlrYWNpICovXHJcbnZhciByZWFjdFNlbmRNZXNzYWdlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlYWN0Q2hhdFNlbmRNZXNzYWdlTGluaycpO1xyXG52YXIgcmVhY3RSZWZyZXNoTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0UmVmcmVzaE1lc3NhZ2VzTGluaycpO1xyXG52YXIgcmVhY3RMb2FkTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0TG9hZE1lc3NhZ2VzTGluaycpO1xyXG52YXIgcmVhY3RHZXRPbGRlck1lc3NhZ2VzID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlYWN0Q2hhdEdldE9sZGVyTWVzc2FnZXNMaW5rJyk7XHJcbi8qIGsgcG9zbMOhbsOtIHpwcsOhdnkqL1xyXG52YXIgcmVhY3RTZW5kTWVzc2FnZUxpbmsgPSByZWFjdFNlbmRNZXNzYWdlLmhyZWY7XHJcbi8qIGsgcHJhdmlkZWxuw6ltdSBkb3RhenUgbmEgenByw6F2eSAqL1xyXG52YXIgcmVhY3RSZWZyZXNoTWVzc2FnZXNMaW5rID0gcmVhY3RSZWZyZXNoTWVzc2FnZXMuaHJlZjtcclxuLyogayBkb3RhenUgbmEgbmHEjXRlbsOtIHpwcsOhdiwga2R5xb4gbmVtw6FtIHphdMOtbSDFvsOhZG7DqSAodHlwaWNreSBwb3NsZWRuw60genByw6F2eSBtZXppIHXFvml2YXRlbGkpICovXHJcbnZhciByZWFjdExvYWRNZXNzYWdlc0xpbmsgPSByZWFjdExvYWRNZXNzYWdlcy5ocmVmO1xyXG4vKiBrIGRvdGF6dSBuYSBzdGFyxaHDrSB6cHLDoXZ5ICovXHJcbnZhciByZWFjdEdldE9sZGVyTWVzc2FnZXNMaW5rID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzLmhyZWY7XHJcbi8qKiBwcmVmaXggcMWZZWQgcGFyYW1ldHJ5IGRvIHVybCAqL1xyXG52YXIgcGFyYW1ldGVyc1ByZWZpeCA9IHJlYWN0U2VuZE1lc3NhZ2UuZGF0YXNldC5wYXJwcmVmaXg7XHJcbi8qKiBvYnZ5a2zDvSBwb8SNZXQgcMWZw61jaG96w61jaCB6cHLDoXYgdiBvZHBvdsSbZGkgdSBwcmF2aWRlbG7DqWhvIGEgaW5pY2nDoWxuw61obyBwb8W+YWRhdmt1IChhbmViIGtvbGlrIHpwcsOhdiBtaSBwxZlpamRlLCBrZHnFviBqaWNoIGplIG5hIHNlcnZlcnUgamXFoXTEmyBkb3N0KSAqL1xyXG52YXIgdXN1YWxPbGRlck1lc3NhZ2VzQ291bnQgPSByZWFjdEdldE9sZGVyTWVzc2FnZXMuZGF0YXNldC5tYXhtZXNzYWdlcztcclxudmFyIHVzdWFsTG9hZE1lc3NhZ2VzQ291bnQgPSByZWFjdExvYWRNZXNzYWdlcy5kYXRhc2V0Lm1heG1lc3NhZ2VzO1xyXG4vKiDEjWFzb3ZhxI0gcHJvIHByYXZpZGVsbsOpIHBvxb5hZGF2a3kgbmEgc2VydmVyICovXHJcbnZhciBUaW1lciA9IFRpbWVyRmFjdG9yeS5uZXdJbnN0YW5jZSgpO1xyXG5cclxuLyoqKioqKioqKioqICBERUZJTklDRSAgKioqKioqKioqKiovXHJcbi8qKiDEjMOhc3Qgb2tuYSwga3RlcsOhIG3DoSBzdmlzbMO9IHBvc3V2bsOtayAtIG9ic2FodWplIHpwcsOhdnksIHRsYcSNw610a28gcHJvIGRvbmHEjcOtdMOhbsOtLi4uICovXHJcbnZhciBNZXNzYWdlc1dpbmRvdyA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlc1dpbmRvd1wiLFxyXG4gIGdldEluaXRpYWxTdGF0ZTogZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4ge21lc3NhZ2VzOiBbXSwgaW5mb01lc3NhZ2VzOiBbXSwgdGhlcmVJc01vcmU6IHRydWUsIGhyZWY6ICcnIH07XHJcbiAgfSxcclxuICBjb21wb25lbnREaWRNb3VudDogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgY29tcG9uZW50ID0gdGhpcztcclxuICAgIE1lc3NhZ2VTdG9yZS5hZGRDaGFuZ2VMaXN0ZW5lcihmdW5jdGlvbigpe1xyXG4gICAgICBjb21wb25lbnQuc2V0U3RhdGUoTWVzc2FnZVN0b3JlLmdldFN0YXRlKCkpO1xyXG4gICAgfSk7XHJcbiAgICBNZXNzYWdlQWN0aW9ucy5jcmVhdGVHZXRJbml0aWFsTWVzc2FnZXMocmVhY3RMb2FkTWVzc2FnZXNMaW5rLCB0aGlzLnByb3BzLnVzZXJDb2RlZElkLCBwYXJhbWV0ZXJzUHJlZml4LCB1c3VhbExvYWRNZXNzYWdlc0NvdW50KTtcclxuICB9LFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbWVzc2FnZXMgPSB0aGlzLnN0YXRlLm1lc3NhZ2VzO1xyXG4gICAgdmFyIGluZm9NZXNzYWdlcyA9IHRoaXMuc3RhdGUuaW5mb01lc3NhZ2VzO1xyXG4gICAgdmFyIG9sZGVzdElkID0gdGhpcy5nZXRPbGRlc3RJZChtZXNzYWdlcyk7XHJcbiAgICB2YXIgdXNlckNvZGVkSWQgPSB0aGlzLnByb3BzLnVzZXJDb2RlZElkO1xyXG4gICAgLyogc2VzdGF2ZW7DrSBvZGthenUgcHJvIHRsYcSNw610a28gKi9cclxuICAgIHZhciBtb3JlQnV0dG9uTGluayA9IHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmsgKyAnJicgKyBwYXJhbWV0ZXJzUHJlZml4ICsgJ2xhc3RJZD0nICsgb2xkZXN0SWQgKyAnJicgKyBwYXJhbWV0ZXJzUHJlZml4ICsgJ3dpdGhVc2VySWQ9JyArIHRoaXMucHJvcHMudXNlckNvZGVkSWQ7XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZXNXaW5kb3dcIn0sIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoTG9hZE1vcmVCdXR0b24sIHtsb2FkSHJlZjogbW9yZUJ1dHRvbkxpbmssIG9sZGVzdElkOiBvbGRlc3RJZCwgdGhlcmVJc01vcmU6IHRoaXMuc3RhdGUudGhlcmVJc01vcmUsIHVzZXJDb2RlZElkOiB1c2VyQ29kZWRJZH0pLCBcclxuICAgICAgICBtZXNzYWdlcy5tYXAoZnVuY3Rpb24obWVzc2FnZSwgaSl7XHJcbiAgICAgICAgICAgIHJldHVybiBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2UsIHtrZXk6IHVzZXJDb2RlZElkICsgJ21lc3NhZ2UnICsgaSwgbWVzc2FnZURhdGE6IG1lc3NhZ2UsIHVzZXJIcmVmOiBtZXNzYWdlLnByb2ZpbGVIcmVmLCBwcm9maWxlUGhvdG9Vcmw6IG1lc3NhZ2UucHJvZmlsZVBob3RvVXJsfSk7XHJcbiAgICAgICAgfSksIFxyXG4gICAgICAgIFxyXG4gICAgICAgIGluZm9NZXNzYWdlcy5tYXAoZnVuY3Rpb24obWVzc2FnZSwgaSl7XHJcbiAgICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoSW5mb01lc3NhZ2UsIHtrZXk6IHVzZXJDb2RlZElkICsgJ2luZm8nICsgaSwgbWVzc2FnZURhdGE6IG1lc3NhZ2V9KTtcclxuICAgICAgICAgIH0pXHJcbiAgICAgICAgXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBnZXRPbGRlc3RJZDogZnVuY3Rpb24obWVzc2FnZXMpe1xyXG4gICAgcmV0dXJuIChtZXNzYWdlc1swXSkgPyBtZXNzYWdlc1swXS5pZCA6IDkwMDcxOTkyNTQ3NDA5OTE7IC8qbmFzdGF2ZW7DrSBob2Rub3R5IG5lYm8gbWF4aW3DoWxuw60gaG9kbm90eSwga2R5xb4gbmVuw60qL1xyXG4gIH1cclxufSk7XHJcblxyXG52YXIgSW5mb01lc3NhZ2UgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiSW5mb01lc3NhZ2VcIixcclxuICByZW5kZXI6IGZ1bmN0aW9uKCl7XHJcbiAgICAgIHJldHVybihSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcImluZm8tbWVzc2FnZVwifSwgdGhpcy5wcm9wcy5tZXNzYWdlRGF0YS50ZXh0KSk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBKZWRuYSB6cHLDoXZhLiAqL1xyXG52YXIgTWVzc2FnZSA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBtZXNzYWdlID0gdGhpcy5wcm9wcy5tZXNzYWdlRGF0YTtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlXCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiB0aGlzLnByb3BzLnVzZXJIcmVmLCB1c2VyTmFtZTogbWVzc2FnZS5uYW1lLCBwcm9maWxlUGhvdG9Vcmw6IHRoaXMucHJvcHMucHJvZmlsZVBob3RvVXJsfSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlQXJyb3dcIn0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwicFwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VUZXh0XCJ9LCBcclxuICAgICAgICAgIG1lc3NhZ2UudGV4dCwgXHJcbiAgICAgICAgICBtZXNzYWdlLmltYWdlcy5tYXAoZnVuY3Rpb24oaW1hZ2UsIGkpe1xyXG4gICAgICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiwge3NyYzogaW1hZ2UudXJsLCB3aWR0aDogaW1hZ2Uud2lkdGgsIGtleTogbWVzc2FnZS5pZCArICdtZXNzYWdlJyArIGl9KTtcclxuICAgICAgICAgICAgfSksIFxyXG4gICAgICAgICAgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VEYXRldGltZVwifSwgbWVzc2FnZS5zZW5kZWREYXRlKVxyXG4gICAgICAgICksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJjbGVhclwifSlcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqIERvbmHEjcOtdGFjw60gdGxhxI3DrXRrbyAqL1xyXG52YXIgTG9hZE1vcmVCdXR0b24gPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTG9hZE1vcmVCdXR0b25cIixcclxuICByZW5kZXI6IGZ1bmN0aW9uKCkge1xyXG4gICAgaWYoIXRoaXMucHJvcHMudGhlcmVJc01vcmUpeyByZXR1cm4gbnVsbDt9XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcImxvYWRNb3JlQnV0dG9uIGJ0bi1tYWluIGxvYWRpbmdidXR0b24gdWktYnRuXCIsIG9uQ2xpY2s6IHRoaXMuaGFuZGxlQ2xpY2t9LCBcbiAgICAgICAgXCJOYcSNw61zdCBwxZllZGNob3rDrSB6cHLDoXZ5XCJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBoYW5kbGVDbGljazogZnVuY3Rpb24oKXtcclxuICAgIE1lc3NhZ2VBY3Rpb25zLmNyZWF0ZUdldE9sZGVyTWVzc2FnZXMocmVhY3RHZXRPbGRlck1lc3NhZ2VzTGluaywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgdGhpcy5wcm9wcy5vbGRlc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCwgdXN1YWxPbGRlck1lc3NhZ2VzQ291bnQpO1xyXG4gIH1cclxufSk7XHJcblxyXG4vKiogRm9ybXVsw6HFmSBwcm8gb2Rlc8OtbMOhbsOtIHpwcsOhdiAqL1xyXG52YXIgTmV3TWVzc2FnZUZvcm0gPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTmV3TWVzc2FnZUZvcm1cIixcclxuICByZW5kZXI6IGZ1bmN0aW9uKCkge1xyXG4gICAgdmFyIGxvZ2dlZFVzZXIgPSB0aGlzLnByb3BzLmxvZ2dlZFVzZXI7XHJcbiAgICB2YXIgc2xhcEJ1dHRvbiA9ICcnO1xyXG4gICAgY29uc29sZS5sb2cobG9nZ2VkVXNlcik7XHJcbiAgICBpZiAobG9nZ2VkVXNlci5hbGxvd2VkVG9TbGFwKXtcclxuICAgICAgc2xhcEJ1dHRvbiA9IFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJhXCIsIHtocmVmOiBcIiNcIiwgdGl0bGU6IFwiUG9zbGF0IGZhY2t1XCIsIGNsYXNzTmFtZTogXCJzZW5kU2xhcFwiLCBvbkNsaWNrOiB0aGlzLnNlbmRTbGFwfSlcclxuICAgIH1cclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJuZXdNZXNzYWdlXCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiBsb2dnZWRVc2VyLmhyZWYsIHVzZXJOYW1lOiBsb2dnZWRVc2VyLm5hbWUsIHByb2ZpbGVQaG90b1VybDogbG9nZ2VkVXNlci5wcm9maWxlUGhvdG9Vcmx9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VBcnJvd1wifSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJmb3JtXCIsIHtvblN1Ym1pdDogdGhpcy5vblN1Ym1pdH0sIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VJbnB1dENvbnRhaW5lclwifSwgXHJcbiAgICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbnB1dFwiLCB7dHlwZTogXCJ0ZXh0XCIsIGNsYXNzTmFtZTogXCJtZXNzYWdlSW5wdXRcIn0pLCBcclxuICAgICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcImlucHV0SW50ZXJmYWNlXCJ9LCBcclxuICAgICAgICAgICAgICBzbGFwQnV0dG9uXHJcbiAgICAgICAgICAgICksIFxyXG4gICAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2xlYXJcIn0pXHJcbiAgICAgICAgICApLCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbnB1dFwiLCB7dHlwZTogXCJzdWJtaXRcIiwgY2xhc3NOYW1lOiBcImJ0bi1tYWluIG1lZGl1bSBidXR0b25cIiwgdmFsdWU6IFwiT2Rlc2xhdFwifSlcclxuICAgICAgICApXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBzZW5kU2xhcDogZnVuY3Rpb24oZSl7XHJcbiAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICBNZXNzYWdlQWN0aW9ucy5jcmVhdGVTZW5kTWVzc2FnZShyZWFjdFNlbmRNZXNzYWdlTGluaywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgTWVzc2FnZUNvbnN0YW50cy5TRU5EX1NMQVAsIGdldExhc3RJZCgpKTtcclxuICB9LFxyXG4gIG9uU3VibWl0OiBmdW5jdGlvbihlKXsvKiBWZXptZSB6cHLDoXZ1IHplIHN1Ym1pdHUgYSBwb8WhbGUgamkuIFRha8OpIHNtYcW+ZSB6cHLDoXZ1IG5hcHNhbm91IHYgaW5wdXR1LiAqL1xyXG4gICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xyXG4gICAgdmFyIGlucHV0ID0gZS50YXJnZXQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnbWVzc2FnZUlucHV0JylbMF07XHJcbiAgICB2YXIgbWVzc2FnZSA9IGlucHV0LnZhbHVlO1xyXG4gICAgaWYobWVzc2FnZSA9PSB1bmRlZmluZWQgfHwgbWVzc2FnZS50cmltKCkgPT0gJycpIHJldHVybjtcclxuICAgIGlucHV0LnZhbHVlID0gJyc7XHJcbiAgICBNZXNzYWdlQWN0aW9ucy5jcmVhdGVTZW5kTWVzc2FnZShyZWFjdFNlbmRNZXNzYWdlTGluaywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgbWVzc2FnZSwgZ2V0TGFzdElkKCkpO1xyXG4gIH1cclxufSk7XHJcblxyXG4vKipcclxuICogaW5pY2lhbGl6dWplIMSNYXNvdmHEjSBwcmF2aWRlbG7EmyBzZSBkb3RhenVqw61jw60gbmEgbm92w6kgenByw6F2eSB2IHrDoXZpc2xvc3RpIG5hIHRvbSwgamFrIHNlIG3Em27DrSBkYXRhIHYgTWVzc2FnZVN0b3JlXHJcbiAqIEBwYXJhbSB7c3RyaW5nfSB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGUsIHNlIGt0ZXLDvW0gc2kgcMOtxaF1XHJcbiAqL1xyXG52YXIgaW5pdGlhbGl6ZUNoYXRUaW1lciA9IGZ1bmN0aW9uKHVzZXJDb2RlZElkKXtcclxuICBNZXNzYWdlU3RvcmUuYWRkQ2hhbmdlTGlzdGVuZXIoZnVuY3Rpb24oKXtcclxuICAgIHZhciBzdGF0ZSA9IE1lc3NhZ2VTdG9yZS5nZXRTdGF0ZSgpO1xyXG4gICAgaWYoc3RhdGUuZGF0YVZlcnNpb24gPT0gMSl7LyogZGF0YSBzZSBwb3BydsOpIHptxJtuaWxhICovXHJcbiAgICAgIFRpbWVyLm1heGltdW1JbnRlcnZhbCA9IDYwMDAwO1xyXG4gICAgICBUaW1lci5pbml0aWFsSW50ZXJ2YWwgPSAzMDAwO1xyXG4gICAgICBUaW1lci5pbnRlcnZhbEluY3Jhc2UgPSAyMDAwO1xyXG4gICAgICBUaW1lci5sYXN0SWQgPSBnZXRMYXN0SWQoKTtcclxuICAgICAgVGltZXIudGljayA9IGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgTWVzc2FnZUFjdGlvbnMuY3JlYXRlUmVmcmVzaE1lc3NhZ2VzKHJlYWN0UmVmcmVzaE1lc3NhZ2VzTGluaywgdXNlckNvZGVkSWQsIFRpbWVyLmxhc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCk7XHJcbiAgICAgIH07XHJcbiAgICAgIFRpbWVyLnN0YXJ0KCk7XHJcbiAgICB9ZWxzZXsvKiBrZHnFviBzZSBkYXRhIG5lem3Em25pbGEgcG9wcnbDqSwgYWxlIHVyxI1pdMSbIHNlIHptxJtuaWxhICovXHJcbiAgICAgIFRpbWVyLmxhc3RJZCA9IGdldExhc3RJZCgpO1xyXG4gICAgICBUaW1lci5yZXNldFRpbWUoKTtcclxuICAgIH1cclxuICB9KTtcclxuXHJcbn07XHJcblxyXG4vKipcclxuICogVnLDoXTDrSBwb3NsZWRuw60gem7DoW3DqSBpZFxyXG4gKiBAcmV0dXJuIHtpbnR9IHBvc2xlZG5pIHpuw6Ftw6kgaWRcclxuICovXHJcbnZhciBnZXRMYXN0SWQgPSBmdW5jdGlvbigpIHtcclxuICB2YXIgc3RhdGUgPSBNZXNzYWdlU3RvcmUuZ2V0U3RhdGUoKTtcclxuICBpZihzdGF0ZS5tZXNzYWdlcy5sZW5ndGggPiAwKXtcclxuICAgIHJldHVybiBzdGF0ZS5tZXNzYWdlc1tzdGF0ZS5tZXNzYWdlcy5sZW5ndGggLSAxXS5pZDtcclxuICB9ZWxzZXtcclxuICAgIHJldHVybiAwO1xyXG4gIH1cclxufVxyXG5cclxubW9kdWxlLmV4cG9ydHMgPSB7XHJcbiAgLyoqIE9rbm8gY2Vsw6lobyBjaGF0dSBzIGplZG7DrW0gdcW+aXZhdGVsZW0gKi9cclxuICBDaGF0V2luZG93OiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiQ2hhdFdpbmRvd1wiLFxyXG4gICAgY29tcG9uZW50RGlkTW91bnQ6IGZ1bmN0aW9uKCkge1xyXG4gICAgICBpbml0aWFsaXplQ2hhdFRpbWVyKHRoaXMucHJvcHMudXNlckNvZGVkSWQpO1xyXG4gICAgICBNZXNzYWdlQWN0aW9ucy5yZWxvYWRXaW5kb3dVbmxvYWQoKTtcclxuICAgIH0sXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2hhdFdpbmRvd1wifSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2VzV2luZG93LCB7dXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE5ld01lc3NhZ2VGb3JtLCB7bG9nZ2VkVXNlcjogdGhpcy5wcm9wcy5sb2dnZWRVc2VyLCB1c2VyQ29kZWRJZDogdGhpcy5wcm9wcy51c2VyQ29kZWRJZH0pXHJcbiAgICAgICAgKVxyXG4gICAgICApXHJcbiAgICB9XHJcbiAgfSlcclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG5cclxuICAvKiogS29tcG9uZW50YSBuYSBwcm9maWxvdm91IGZvdGt1ICovXHJcbiAgUHJvZmlsZVBob3RvOiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiUHJvZmlsZVBob3RvXCIsXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiYVwiLCB7Y2xhc3NOYW1lOiBcImdlbmVyYXRlZFByb2ZpbGVcIiwgaHJlZjogdGhpcy5wcm9wcy5wcm9maWxlTGluaywgdGl0bGU6IHRoaXMucHJvcHMudXNlck5hbWV9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiwge3NyYzogdGhpcy5wcm9wcy5wcm9maWxlUGhvdG9Vcmx9KVxyXG4gICAgICAgIClcclxuICAgICAgKTtcclxuICAgIH1cclxuICB9KVxyXG5cclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqIFTFmcOtZGEgemFqacWhxaV1asOtY8OtIHByYXZpZGVsbsOpIHRpa3lcclxuICovXHJcblxyXG4vKiBnbG9iYWwgUmVhY3QgKi8vKiBhYnkgTmV0YmVhbnMgbmV2eWhhem92YWwgY2h5Ynkga3bFr2xpIG5lZGVrbGFyb3ZhbsOpIHByb23Em25uw6kgKi9cclxuLyoqL1xyXG4vKiBUxZnDrWRhIHphamnFocWldWrDrWPDrSBwcmF2aWRlbG7DqSB0aWt5LCBrdGVyw6kgc2UgbW9ob3UgcyBrYcW+ZMO9bSB0aWtudXTDrW0gcHJvZGx1xb5vdmF0ICovXHJcbmZ1bmN0aW9uIFRpbWVyKCkge1xyXG4gIC8qXHJcbiAgICAgICEhISBORU3EmsWHVEUgVFlUTyBQQVJBTUVUUlkgUMWYw41NTyBWIFRPTVRPIFNPVUJPUlUsIFpNxJrFh1RFIEpFIFUgVkHFoMONIElOU1RBTkNFIFRJTUVSVSAhISFcclxuICAqL1xyXG4gIHRoaXMuY3VycmVudEludGVydmFsID0gMTAwMDsgLyogYWt0dcOhbG7DrSDEjWVrw6Fuw60gbWV6aSB0aWt5ICovXHJcbiAgdGhpcy5pbml0aWFsSW50ZXJ2YWwgPSAxMDAwOyAvKiBwb8SNw6F0ZcSNbsOtIGludGVydmFsICovXHJcbiAgdGhpcy5pbnRlcnZhbEluY3Jhc2UgPSAwOy8qIHp2w73FoWVuw60gaW50ZXJ2YWx1IHBvIGthxb5kw6ltIHRpa3UgKi9cclxuICB0aGlzLm1heGltdW1JbnRlcnZhbCA9IDIwMDAwOy8qIG1heGltw6FsbsOtIGludGVydmFsICovXHJcbiAgdGhpcy5ydW5uaW5nID0gZmFsc2U7IC8qIGluZGlrw6F0b3IsIHpkYSB0aW1lciBixJvFvsOtICovXHJcbiAgdGhpcy50aWNrID0gZnVuY3Rpb24oKXt9Oy8qIGZ1bmtjZSwgY28gc2Ugdm9sw6EgcMWZaSBrYcW+ZMOpbSB0aWt1ICovXHJcbiAgdGhpcy5zdGFydCA9IGZ1bmN0aW9uKCl7LyogZnVua2NlLCBrdGVyw6Egc3B1c3TDrSDEjWFzb3ZhxI0gKi9cclxuICAgIGlmKCF0aGlzLnJ1bm5pbmcpe1xyXG4gICAgICB0aGlzLnJ1bm5pbmcgPSB0cnVlO1xyXG4gICAgICB0aGlzLnJlc2V0VGltZSgpO1xyXG4gICAgICB0aGlzLnJlY3Vyc2l2ZSgpO1xyXG4gICAgfVxyXG4gIH07XHJcbiAgdGhpcy5zdG9wID0gZnVuY3Rpb24oKXsvKiBmdW5rY2UsIGt0ZXLDoSB0aW1lciB6YXN0YXbDrSovXHJcbiAgICB0aGlzLnJ1bm5pbmcgPSBmYWxzZTtcclxuICB9O1xyXG4gIHRoaXMucmVzZXRUaW1lID0gZnVuY3Rpb24oKXsvKiBmdW5rY2UsIGt0ZXJvdSB2eXJlc2V0dWppIMSNZWvDoW7DrSBuYSBwb8SNw6F0ZcSNbsOtIGhvZG5vdHUgKi9cclxuICAgIHRoaXMuY3VycmVudEludGVydmFsID0gdGhpcy5pbml0aWFsSW50ZXJ2YWw7XHJcbiAgfTtcclxuICB0aGlzLnJlY3Vyc2l2ZSA9IGZ1bmN0aW9uKCl7LyogbmVwxZlla3LDvXZhdCwgZnVua2NlLCBrdGVyw6EgZMSbbMOhIHNtecSNa3UgKi9cclxuICAgIGlmKHRoaXMucnVubmluZyl7XHJcbiAgICAgIHZhciB0aW1lciA9IHRoaXM7XHJcbiAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKXtcclxuICAgICAgICB0aW1lci50aWNrKCk7XHJcbiAgICAgICAgdGltZXIuY3VycmVudEludGVydmFsID0gTWF0aC5taW4odGltZXIuY3VycmVudEludGVydmFsICsgdGltZXIuaW50ZXJ2YWxJbmNyYXNlLCB0aW1lci5tYXhpbXVtSW50ZXJ2YWwpO1xyXG4gICAgICAgIHRpbWVyLnJlY3Vyc2l2ZSgpO1xyXG4gICAgICB9LCB0aW1lci5jdXJyZW50SW50ZXJ2YWwpO1xyXG4gICAgfVxyXG4gIH07XHJcblxyXG59XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuICBuZXdJbnN0YW5jZTogZnVuY3Rpb24oKXtcclxuICAgIHJldHVybiBuZXcgVGltZXIoKTtcclxuICB9XHJcbn1cclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqXHJcbiAqIFRlbnRvIHNvdWJvciB6YXN0xZllxaF1amUgZmx1eCBha2NlIHNvdXZpc2Vqw61jw60gc2UgesOtc2vDoXbDoW7DrW0genByw6F2LiBUYWvDqSB6cHJvc3TFmWVka292w6F2w6Ega29tdW5pa2FjaSBzZSBzZXJ2ZXJlbS5cclxuICovXHJcblxyXG4gdmFyIGRpc3BhdGNoZXIgPSByZXF1aXJlKCcuLi8uLi9kaXNwYXRjaGVyL2RhdGVub2RlRGlzcGF0Y2hlcicpO1xyXG4gdmFyIGNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMnKTtcclxuIHZhciBFdmVudEVtaXR0ZXIgPSByZXF1aXJlKCdldmVudHMnKS5FdmVudEVtaXR0ZXI7XHJcblxyXG52YXIgQWN0aW9uVHlwZXMgPSBjb25zdGFudHMuQWN0aW9uVHlwZXNcclxuLyogemFteWvDoW7DrSBvxaFldMWZdWrDrWPDrSBzb3VixJvFvm7DqSBwb3Nsw6Fuw60gcG/FvmFkYXZrdSAqL1xyXG52YXIgYWpheExvY2sgPSBmYWxzZTtcclxuXHJcbm1vZHVsZS5leHBvcnRzID0geyAgLyoqXHJcbiAgICogWsOtc2vDoSB6ZSBzZXJ2ZXJ1IHBvc2xlZG7DrWNoIG7Em2tvbGlrIHByb2LEm2hsw71jaCB6cHLDoXYgcyB1xb5pdmF0ZWxlbSBzIGRhbsO9bSBpZFxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgdXJsLCBrdGVyw6kgc2UgcHTDoW0gbmEgenByw6F2eVxyXG4gICAqIEBwYXJhbSB7aW50fSB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGUsIHNlIGt0ZXLDvW0gc2kgcMOtxaF1XHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IHBhcmFtZXRlcnNQcmVmaXggcHJlZml4IHDFmWVkIHBhcmFtZXRyeSB2IHVybFxyXG4gICAqIEBwYXJhbSB7aW50fSB1c3VhbExvYWRNZXNzYWdlc0NvdW50ICBvYnZ5a2zDvSBwb8SNZXQgcMWZw61jaG96w61jaCB6cHLDoXYgdiBvZHBvdsSbZGlcclxuICAgKi9cclxuICBjcmVhdGVHZXRJbml0aWFsTWVzc2FnZXM6IGZ1bmN0aW9uKHVybCwgdXNlckNvZGVkSWQsIHBhcmFtZXRlcnNQcmVmaXgsIHVzdWFsTG9hZE1lc3NhZ2VzQ291bnQpe1xyXG4gICAgdmFyIGRhdGEgPSB7fTtcclxuICBcdGRhdGFbcGFyYW1ldGVyc1ByZWZpeCArICdmcm9tSWQnXSA9IHVzZXJDb2RlZElkO1xyXG4gICAgdGhpcy5ibG9ja1dpbmRvd1VubG9hZCgnSmXFoXTEmyBzZSBuYcSNw610YWrDrSB6cHLDoXZ5LCBvcHJhdmR1IGNoY2V0ZSBvZGVqw610PycpO1xyXG4gICAgdmFyIGV4cG9ydE9iamVjdCA9IHRoaXM7XHJcbiAgICAkLmdldEpTT04odXJsLCBkYXRhLCBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgIGlmKHJlc3VsdC5sZW5ndGggPT0gMCkge1xyXG4gICAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk5PX0lOSVRJQUxfTUVTU0FHRVNfQVJSSVZFRFxyXG4gICAgICAgICAgfSk7XHJcbiAgICAgICAgfWVsc2V7XHJcbiAgICAgICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICAgICAgdHlwZTogQWN0aW9uVHlwZXMuT0xERVJfTUVTU0FHRVNfQVJSSVZFRCxcclxuICAgICAgICAgICAgZGF0YTogcmVzdWx0LFxyXG4gICAgICAgICAgICB1c2VyQ29kZWRJZCA6IHVzZXJDb2RlZElkLFxyXG4gICAgICAgICAgICB1c3VhbE1lc3NhZ2VzQ291bnQgOiB1c3VhbExvYWRNZXNzYWdlc0NvdW50XHJcbiAgICAgICAgICAgIC8qIHRhZHkgYnljaCBwxZnDrXBhZG7EmyBwxZlpZGFsIGRhbMWhw60gZGF0YSAqL1xyXG4gICAgICAgICAgfSk7XHJcbiAgICAgICAgfVxyXG4gICAgfSkuZG9uZShmdW5jdGlvbigpIHtcclxuICAgICAgZXhwb3J0T2JqZWN0LnJlbG9hZFdpbmRvd1VubG9hZCgpO1xyXG4gICAgfSkuZmFpbChmdW5jdGlvbigpe1xyXG4gICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5NRVNTQUdFX0VSUk9SLFxyXG4gICAgICAgIGVycm9yTWVzc2FnZTogJ1pwcsOhdnkgc2UgYm9odcW+ZWwgbmVwb2RhxZlpbG8gbmHEjcOtc3QuIFprdXN0ZSB0byB6bm92dSBwb3pkxJtqaS4nXHJcbiAgICAgIH0pO1xyXG4gICAgfSk7XHJcbiAgfSxcclxuXHJcbiAgLyoqXHJcbiAgICogWsOtc2vDoSB6ZSBzZXJ2ZXJ1IG7Em2tvbGlrIHN0YXLFocOtY2ggenByw6F2XHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IHVybCB1cmwsIGt0ZXLDqSBzZSBwdMOhbSBuYSB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gICAqIEBwYXJhbSAge2ludH0gICBvbGRlc3RJZCBpZCBuZWpzdGFyxaHDrSB6cHLDoXZ5IChuZWptZW7FocOtIHpuw6Ftw6kgaWQpXHJcbiAgICogQHBhcmFtICB7c3RyaW5nfSBwYXJhbWV0ZXJzUHJlZml4IHByZWZpeCBwxZllZCBwYXJhbWV0cnkgdiB1cmxcclxuICAgKiBAcGFyYW0ge2ludH0gdXN1YWxPbGRlck1lc3NhZ2VzQ291bnQgIG9idnlrbMO9IHBvxI1ldCBwxZnDrWNob3rDrWNoIHpwcsOhdiB2IG9kcG92xJtkaVxyXG4gICAqL1xyXG4gIGNyZWF0ZUdldE9sZGVyTWVzc2FnZXM6IGZ1bmN0aW9uKHVybCwgdXNlckNvZGVkSWQsIG9sZGVzdElkLCBwYXJhbWV0ZXJzUHJlZml4LCB1c3VhbE9sZGVyTWVzc2FnZXNDb3VudCl7XHJcbiAgICBhamF4TG9jayA9IHRydWU7XHJcbiAgICB2YXIgZGF0YSA9IHt9O1xyXG4gIFx0ZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ2xhc3RJZCddID0gb2xkZXN0SWQ7XHJcbiAgICBkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnd2l0aFVzZXJJZCddID0gdXNlckNvZGVkSWQ7XHJcbiAgICAkLmdldEpTT04odXJsLCBkYXRhLCBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgIGFqYXhMb2NrID0gZmFsc2U7XHJcbiAgICAgICAgaWYocmVzdWx0Lmxlbmd0aCA9PSAwKSByZXR1cm47XHJcbiAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5PTERFUl9NRVNTQUdFU19BUlJJVkVELFxyXG4gICAgICAgICAgZGF0YTogcmVzdWx0LFxyXG4gICAgICAgICAgdXNlckNvZGVkSWQgOiB1c2VyQ29kZWRJZCxcclxuICAgICAgICAgIG9sZGVyc0lkIDogb2xkZXN0SWQsXHJcbiAgICAgICAgICB1c3VhbE1lc3NhZ2VzQ291bnQgOiB1c3VhbE9sZGVyTWVzc2FnZXNDb3VudFxyXG4gICAgICAgIH0pO1xyXG4gICAgfSkuZmFpbChmdW5jdGlvbigpe1xyXG4gICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5NRVNTQUdFX0VSUk9SLFxyXG4gICAgICAgIGVycm9yTWVzc2FnZTogJ1pwcsOhdnkgc2UgYm9odcW+ZWwgbmVwb2RhxZlpbG8gbmHEjcOtc3QuIFprdXN0ZSB0byB6bm92dSBwb3pkxJtqaS4nXHJcbiAgICAgIH0pO1xyXG4gICAgfSk7XHJcbiAgfSxcclxuXHJcbiAgLyoqXHJcbiAgICogUG/FoWxlIG5hIHNlcnZlciB6cHLDoXZ1LlxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgdXJsLCBrdGVyw6kgc2UgcHTDoW0gbmEgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtTdHJpbmd9IG1lc3NhZ2UgdGV4dCB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7aW50fSBsYXN0SWQgcG9zbGVkbsOtIHpuw6Ftw6kgaWRcclxuICAgKi9cclxuICBjcmVhdGVTZW5kTWVzc2FnZTogZnVuY3Rpb24odXJsLCB1c2VyQ29kZWRJZCwgbWVzc2FnZSwgbGFzdElkKXtcclxuICAgIGFqYXhMb2NrID0gdHJ1ZTtcclxuICAgIHZhciBkYXRhID0ge1xyXG4gICAgICB0bzogdXNlckNvZGVkSWQsXHJcbiAgICAgIHR5cGU6ICd0ZXh0TWVzc2FnZScsXHJcbiAgICAgIHRleHQ6IG1lc3NhZ2UsXHJcbiAgICAgIGxhc3RpZDogbGFzdElkXHJcbiAgICB9O1xyXG4gICAgdGhpcy5ibG9ja1dpbmRvd1VubG9hZCgnWnByw6F2YSBzZSBzdMOhbGUgb2Rlc8OtbMOhLCBwcm9zw61tZSBwb8SNa2VqdGUgbsSba29saWsgc2VrdW5kIGEgcGFrIHRvIHprdXN0ZSB6bm92YS4nKTtcclxuICAgIHZhciBleHBvcnRPYmplY3QgPSB0aGlzO1xyXG4gICAgdmFyIGpzb24gPSBKU09OLnN0cmluZ2lmeShkYXRhKTtcclxuICBcdFx0JC5hamF4KHtcclxuICBcdFx0XHRkYXRhVHlwZTogXCJqc29uXCIsXHJcbiAgXHRcdFx0dHlwZTogJ1BPU1QnLFxyXG4gIFx0XHRcdHVybDogdXJsLFxyXG4gIFx0XHRcdGRhdGE6IGpzb24sXHJcbiAgXHRcdFx0Y29udGVudFR5cGU6ICdhcHBsaWNhdGlvbi9qc29uOyBjaGFyc2V0PXV0Zi04JyxcclxuICBcdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk5FV19NRVNTQUdFU19BUlJJVkVELFxyXG4gICAgICAgICAgICBkYXRhOiByZXN1bHQsXHJcbiAgICAgICAgICAgIHVzZXJDb2RlZElkIDogdXNlckNvZGVkSWRcclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH0sXHJcbiAgICAgICAgY29tcGxldGU6IGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgICBhamF4TG9jayA9IGZhbHNlO1xyXG4gICAgICAgICAgZXhwb3J0T2JqZWN0LnJlbG9hZFdpbmRvd1VubG9hZCgpO1xyXG4gICAgICAgIH0sXHJcbiAgICAgICAgZXJyb3I6IGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICAgICAgdHlwZTogQWN0aW9uVHlwZXMuTUVTU0FHRV9FUlJPUixcclxuICAgICAgICAgICAgZXJyb3JNZXNzYWdlOiAnVmHFoWkgenByw6F2dSBzZSBib2h1xb5lbCBuZXBvZGHFmWlsbyBvZGVzbGF0LiBaa3VzdGUgdG8gem5vdnUgcG96ZMSbamkuJ1xyXG4gICAgICAgICAgfSk7XHJcbiAgICAgICAgfVxyXG4gIFx0XHR9KTtcclxuICB9LFxyXG5cclxuICAvKipcclxuICAgKiBaZXB0w6Egc2Ugc2VydmVydSBuYSBub3bDqSB6cHLDoXZ5XHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IHVybCB1cmwsIGt0ZXLDqSBzZSBwdMOhbSBuYSB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gICAqIEBwYXJhbSAge2ludH0gbGFzdElkIHBvc2xlZG7DrSB6bsOhbcOpIGlkXHJcbiAgICogQHBhcmFtICB7c3RyaW5nfSBwYXJhbWV0ZXJzUHJlZml4IHByZWZpeCBwxZllZCBwYXJhbWV0cnkgdiB1cmxcclxuICAgKi9cclxuICBjcmVhdGVSZWZyZXNoTWVzc2FnZXM6IGZ1bmN0aW9uKHVybCwgdXNlckNvZGVkSWQsIGxhc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCl7XHJcbiAgICBpZihhamF4TG9jaykgcmV0dXJuO1xyXG4gICAgdmFyIGRhdGEgPSB7fTtcclxuICBcdGRhdGFbcGFyYW1ldGVyc1ByZWZpeCArICdsYXN0aWQnXSA9IGxhc3RJZDtcclxuICAgIGRhdGFbcGFyYW1ldGVyc1ByZWZpeCArICdyZWFkZWRNZXNzYWdlcyddID0gW2xhc3RJZF07XHJcbiAgICAkLmdldEpTT04odXJsLCBkYXRhLCBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgIGlmKHJlc3VsdC5sZW5ndGggPT0gMCkgcmV0dXJuO1xyXG4gICAgICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gICAgICAgICAgdHlwZTogQWN0aW9uVHlwZXMuTkVXX01FU1NBR0VTX0FSUklWRUQsXHJcbiAgICAgICAgICBkYXRhOiByZXN1bHQsXHJcbiAgICAgICAgICB1c2VyQ29kZWRJZCA6IHVzZXJDb2RlZElkXHJcbiAgICAgICAgfSk7XHJcbiAgICB9KS5mYWlsKGZ1bmN0aW9uKCl7XHJcbiAgICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk1FU1NBR0VfRVJST1IsXHJcbiAgICAgICAgZXJyb3JNZXNzYWdlOiAnWnByw6F2eSBzZSBib2h1xb5lbCBuZXBvZGHFmWlsbyBuYcSNw61zdC4gWmt1c3RlIHRvIHpub3Z1IHBvemTEm2ppLidcclxuICAgICAgfSk7XHJcbiAgICB9KTtcclxuICB9LFxyXG5cclxuICAvKipcclxuICBcdCAqIFDFmWkgcG9rdXN1IHphdsWZw610IG5lYm8gb2Jub3ZpdCBva25vIHNlIHplcHTDoSB1xb5pdmF0ZWxlLFxyXG4gIFx0ICogemRhIGNoY2Ugb2tubyBza3V0ZcSNbsSbIHphdsWZw610L29ibm92aXQuIFRvdG8gZMSbbMOhIHYga2HFvmTDqW0gcMWZw61wYWTEmywgZG9rdWRcclxuICBcdCAqIHNlIG5lemF2b2zDoSByZWxvYWRXaW5kb3dVbmxvYWRcclxuICBcdCAqIEBwYXJhbSB7U3RyaW5nfSByZWFzb24gZMWvdm9kIHV2ZWRlbsO9IHYgZGlhbG9ndVxyXG4gIFx0ICovXHJcbiAgXHRibG9ja1dpbmRvd1VubG9hZDogZnVuY3Rpb24ocmVhc29uKSB7XHJcbiAgXHRcdHdpbmRvdy5vbmJlZm9yZXVubG9hZCA9IGZ1bmN0aW9uICgpIHtcclxuICBcdFx0XHRyZXR1cm4gcmVhc29uO1xyXG4gIFx0XHR9O1xyXG4gIFx0fSxcclxuXHJcbiAgXHQvKipcclxuICBcdCAqIFZ5cG5lIGhsw61kw6Fuw60gemF2xZllbsOtL29ibm92ZW7DrSBva25hIGEgdnLDoXTDrSBqZWogZG8gcG/EjcOhdGXEjW7DrWhvIHN0YXZ1LlxyXG4gIFx0ICovXHJcbiAgXHRyZWxvYWRXaW5kb3dVbmxvYWQ6IGZ1bmN0aW9uKCkge1xyXG4gIFx0XHR3aW5kb3cub25iZWZvcmV1bmxvYWQgPSBmdW5jdGlvbiAoKSB7XHJcbiAgXHRcdFx0dmFyIHVuc2VuZCA9IGZhbHNlO1xyXG4gIFx0XHRcdCQuZWFjaCgkKFwiLm1lc3NhZ2VJbnB1dFwiKSwgZnVuY3Rpb24gKCkgey8vcHJvamRlIHZzZWNobnkgdGV4dGFyZWEgY2hhdHVcclxuICBcdFx0XHRcdGlmICgkLnRyaW0oJCh0aGlzKS52YWwoKSkpIHsvL3Uga2F6ZGVobyB6a291bWEgaG9kbm90dSBiZXogd2hpdGVzcGFjdVxyXG4gIFx0XHRcdFx0XHR1bnNlbmQgPSB0cnVlO1xyXG4gIFx0XHRcdFx0fVxyXG4gIFx0XHRcdH0pO1xyXG4gIFx0XHRcdGlmICh1bnNlbmQpIHtcclxuICBcdFx0XHRcdHJldHVybiAnTcOhdGUgcm96ZXBzYW7DvSBwxZnDrXNwxJt2ZWsuIENoY2V0ZSB0dXRvIHN0csOhbmt1IHDFmWVzdG8gb3B1c3RpdD8nO1xyXG4gIFx0XHRcdFx0LyogaGzDocWha2EsIGNvIHNlIG9iamV2w60gcMWZaSBwb2t1c3Ugb2Jub3ZpdC96YXbFmcOtdCBva25vLCB6YXTDrW1jbyBtw6EgdcW+aXZhdGVsIHJvemVwc2Fub3UgenByw6F2dSAqL1xyXG4gIFx0XHRcdH1cclxuICBcdFx0fTtcclxuICBcdH1cclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuXHJcbnZhciBrZXlNaXJyb3IgPSByZXF1aXJlKCdrZXltaXJyb3InKTtcclxuXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG5cclxuICAvKiB0eXB5IGFrY8OtLCBrdGVyw6kgbW9ob3UgbmFzdGF0ICovXHJcbiAgQWN0aW9uVHlwZXM6IGtleU1pcnJvcih7XHJcbiAgICAvKiBDSEFUICovXHJcbiAgICBOT19JTklUSUFMX01FU1NBR0VTX0FSUklWRUQgOiBudWxsLC8qIHDFmWnFoWxhIG9kcG92xJvEjyBwxZlpIHBydm90bsOtbSBuYcSNw610w6Fuw60genByw6F2LCBhbGUgYnlsYSBwcsOhemRuw6EqL1xyXG4gICAgT0xERVJfTUVTU0FHRVNfQVJSSVZFRCA6IG51bGwsLyogcMWZacWhbHkgc3RhcsWhw60gKGRvbmHEjXRlbsOpIHRsYcSNw610a2VtKSB6cHLDoXZ5ICovXHJcbiAgICBORVdfTUVTU0FHRVNfQVJSSVZFRCA6IG51bGwsLyogcMWZacWhbHkgbm92w6kgenByw6F2eSovXHJcbiAgICBNRVNTQUdFX0VSUk9SIDogbnVsbCAvKiBuxJtjbyBzZSBuZXBvdmVkbG8gKi9cclxuICB9KVxyXG5cclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG5cclxuICAvKiBzcGVjacOhbG7DrSDFmWV0xJt6Y2Ugcm96bGnFoW92YW7DqSBjaGF0ZW0gKi9cclxuICBNZXNzYWdlQ29uc3RhbnRzOiB7XHJcbiAgICBTRU5EX1NMQVAgOiAnQCFzbGFwNDQ0JyxcclxuICB9XHJcblxyXG59O1xyXG4iLCIvKlxyXG4gKiBAYXV0aG9yIEphbiBLb3RhbMOtayA8amFuLmtvdGFsaWsucHJvQGdtYWlsLmNvbT5cclxuICogQGNvcHlyaWdodCBDb3B5cmlnaHQgKGMpIDIwMTMtMjAxNSBLdWtyYWwgQ09NUEFOWSBzLnIuby4gICpcclxuICovXHJcblxyXG52YXIgRGlzcGF0Y2hlciA9IHJlcXVpcmUoJ2ZsdXgnKS5EaXNwYXRjaGVyO1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBuZXcgRGlzcGF0Y2hlcigpO1xyXG4iLCIvKlxyXG4gKiBAYXV0aG9yIEphbiBLb3RhbMOtayA8amFuLmtvdGFsaWsucHJvQGdtYWlsLmNvbT5cclxuICogQGNvcHlyaWdodCBDb3B5cmlnaHQgKGMpIDIwMTMtMjAxNSBLdWtyYWwgQ09NUEFOWSBzLnIuby4gICpcclxuICovXHJcblxyXG52YXIgRGlzcGF0Y2hlciA9IHJlcXVpcmUoJy4uLy4uL2Rpc3BhdGNoZXIvZGF0ZW5vZGVEaXNwYXRjaGVyJyk7XHJcbmlmKHR5cGVvZiBqZXN0ICE9PSAndW5kZWZpbmVkJyl7XHJcbiAgIGplc3QuYXV0b01vY2tPZmYoKTsvKiBvYmV6bGnEjWthIGt2xa9saSB0ZXN0b3bDoW7DrSAqL1xyXG4gICB2YXIgY29uc3RhbnRzID0gcmVxdWlyZSgnLi4vLi4vY29uc3RhbnRzL0FjdGlvbkNvbnN0YW50cycpO1xyXG4gICBqZXN0LmF1dG9Nb2NrT24oKTtcclxufWVsc2V7XHJcbiAgdmFyIGNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMnKTtcclxufVxyXG52YXIgTWVzc2FnZUNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9DaGF0Q29uc3RhbnRzJykuTWVzc2FnZUNvbnN0YW50cztcclxuXHJcblxyXG52YXIgRXZlbnRFbWl0dGVyID0gcmVxdWlyZSgnZXZlbnRzJykuRXZlbnRFbWl0dGVyO1xyXG52YXIgYXNzaWduID0gcmVxdWlyZSgnb2JqZWN0LWFzc2lnbicpO1xyXG5cclxudmFyIENIQU5HRV9FVkVOVCA9ICdjaGFuZ2UnO1xyXG5cclxudmFyIF9kYXRhVmVyc2lvbiA9IDA7Lyoga29saWtyw6F0IHNlIHXFviB6bcSbbmlsYSBkYXRhICovXHJcbnZhciBfbWVzc2FnZXMgPSBbXTtcclxudmFyIF9pbmZvTWVzc2FnZXMgPSBbXTtcclxudmFyIF90aGVyZUlzTW9yZSA9IHRydWU7XHJcblxyXG52YXIgTWVzc2FnZVN0b3JlID0gYXNzaWduKHt9LCBFdmVudEVtaXR0ZXIucHJvdG90eXBlLCB7XHJcbiAgLyogdHJpZ2dlciB6bcSbbnkgKi9cclxuICBlbWl0Q2hhbmdlOiBmdW5jdGlvbigpIHtcclxuICAgIF9kYXRhVmVyc2lvbisrO1xyXG4gICAgaWYoX21lc3NhZ2VzLmxlbmd0aCA9PSAwKSBfdGhlcmVJc01vcmUgPSBmYWxzZTtcclxuICAgIHRoaXMuZW1pdChDSEFOR0VfRVZFTlQpO1xyXG4gIH0sXHJcbiAgLyogdG91dG8gbWV0b2RvdSBsemUgcG92xJtzaXQgbGlzdGVuZXIgcmVhZ3Vqw61jw60gcMWZaSB6bcSbbsSbKi9cclxuICBhZGRDaGFuZ2VMaXN0ZW5lcjogZnVuY3Rpb24oY2FsbGJhY2spIHtcclxuICAgIHRoaXMub24oQ0hBTkdFX0VWRU5ULCBjYWxsYmFjayk7XHJcbiAgfSxcclxuICAvKiB0b3V0byBtZXRvZG91IGx6ZSBsaXN0ZW5lciBvZGVqbW91dCovXHJcbiAgcmVtb3ZlQ2hhbmdlTGlzdGVuZXI6IGZ1bmN0aW9uKGNhbGxiYWNrKSB7XHJcbiAgICB0aGlzLnJlbW92ZUxpc3RlbmVyKENIQU5HRV9FVkVOVCwgY2FsbGJhY2spO1xyXG4gIH0sXHJcbiAgLyogdnJhY8OtIHN0YXYgenByw6F2IHYgamVkaW7DqW0gb2JqZWt0dSovXHJcbiAgZ2V0U3RhdGU6IGZ1bmN0aW9uKCkge1xyXG4gICAgcmV0dXJuIHtcclxuICAgICAgbWVzc2FnZXM6IF9tZXNzYWdlcyxcclxuICAgICAgaW5mb01lc3NhZ2VzOiBfaW5mb01lc3NhZ2VzLFxyXG4gICAgICB0aGVyZUlzTW9yZTogX3RoZXJlSXNNb3JlLFxyXG4gICAgICBkYXRhVmVyc2lvbjogX2RhdGFWZXJzaW9uXHJcbiAgICB9O1xyXG4gIH1cclxuXHJcbn0pO1xyXG5cclxuTWVzc2FnZVN0b3JlLmRpc3BhdGNoVG9rZW4gPSBEaXNwYXRjaGVyLnJlZ2lzdGVyKGZ1bmN0aW9uKGFjdGlvbikge1xyXG4gIHZhciB0eXBlcyA9IGNvbnN0YW50cy5BY3Rpb25UeXBlcztcclxuICBzd2l0Y2goYWN0aW9uLnR5cGUpe1xyXG4gICAgY2FzZSB0eXBlcy5ORVdfTUVTU0FHRVNfQVJSSVZFRCA6XHJcbiAgICAgIGFwcGVuZERhdGFJbnRvTWVzc2FnZXMoYWN0aW9uLnVzZXJDb2RlZElkLCBhY3Rpb24uZGF0YSwgYWN0aW9uLnVzdWFsTWVzc2FnZXNDb3VudCk7XHJcbiAgICAgIE1lc3NhZ2VTdG9yZS5lbWl0Q2hhbmdlKCk7XHJcbiAgICAgIGJyZWFrO1xyXG4gICAgY2FzZSB0eXBlcy5PTERFUl9NRVNTQUdFU19BUlJJVkVEIDpcclxuICAgICAgcHJlcGVuZERhdGFJbnRvTWVzc2FnZXMoYWN0aW9uLnVzZXJDb2RlZElkLCBhY3Rpb24uZGF0YSwgYWN0aW9uLnVzdWFsTWVzc2FnZXNDb3VudCk7XHJcbiAgICAgIE1lc3NhZ2VTdG9yZS5lbWl0Q2hhbmdlKCk7XHJcbiAgICAgIGJyZWFrO1xyXG4gICAgY2FzZSB0eXBlcy5OT19JTklUSUFMX01FU1NBR0VTX0FSUklWRUQ6XHJcbiAgICAgIE1lc3NhZ2VTdG9yZS5lbWl0Q2hhbmdlKCk7Lyoga2R5xb4gbmVwxZlpamRvdSDFvsOhZG7DqSB6cHLDoXZ5IHDFmWkgaW5pY2lhbGl6YWNpLCBkw6EgdG8gbmFqZXZvICovXHJcbiAgICAgIGJyZWFrO1xyXG4gICAgY2FzZSB0eXBlcy5NRVNTQUdFX0VSUk9SOlxyXG4gICAgICBhbGVydCgnQ2h5YmEgc8OtdMSbOiAnICsgYWN0aW9uLmVycm9yTWVzc2FnZSk7XHJcbiAgICAgIGJyZWFrO1xyXG4gIH1cclxufSk7XHJcblxyXG4vKipcclxuICogTmFzdGF2w60genByw6F2eSB6ZSBzdGFuZGFyZG7DrWhvIEpTT051IGNoYXR1ICh2aXogZG9rdW1lbnRhY2UpIGRvIHN0YXZ1IHRvaG90byBTdG9yZSB6YSBleGlzdHVqw61jw60genByw6F2eS5cclxuICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICogQHBhcmFtICB7anNvbn0ganNvbkRhdGEgIGRhdGEgemUgc2VydmVydVxyXG4gKi9cclxudmFyIGFwcGVuZERhdGFJbnRvTWVzc2FnZXMgPSBmdW5jdGlvbih1c2VyQ29kZWRJZCwganNvbkRhdGEpe1xyXG4gIHZhciByZXN1bHQgPSBqc29uRGF0YVt1c2VyQ29kZWRJZF07XHJcbiAgdmFyIHJlc3VsdE1lc3NhZ2VzID0gZmlsdGVySW5mb01lc3NhZ2VzKHJlc3VsdC5tZXNzYWdlcyk7XHJcbiAgcmVzdWx0TWVzc2FnZXMgPSBtb2RpZnlNZXNzYWdlcyhyZXN1bHRNZXNzYWdlcyk7XHJcbiAgX21lc3NhZ2VzID0gX21lc3NhZ2VzLmNvbmNhdChyZXN1bHRNZXNzYWdlcyk7XHJcbn07XHJcblxyXG4vKipcclxuICogTmFzdGF2w60genByw6F2eSB6ZSBzdGFuZGFyZG7DrWhvIEpTT051IGNoYXR1ICh2aXogZG9rdW1lbnRhY2UpIGRvIHN0YXZ1IHRvaG90byBTdG9yZSBwxZllZCBleGlzdHVqw61jw60genByw6F2eS5cclxuICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICogQHBhcmFtICB7anNvbn0ganNvbkRhdGEgIGRhdGEgemUgc2VydmVydVxyXG4gKiBAcGFyYW0gIHtpbnR9IHVzdWFsTWVzc2FnZXNDb3VudCBvYnZ5a2zDvSBwb8SNZXQgenByw6F2IC0gcG9rdWQgamUgZG9kcsW+ZW4sIHphaG9kw60gbmVqc3RhcsWhw60genByw6F2dSAocG9rdWQgamUgenByw6F2IGRvc3RhdGVrKVxyXG4gKiBhIGtvbXBvbmVudMSbIHBvZGxlIHRvaG8gbmFzdGF2w60gc3Rhdiwgxb5lIG5hIHNlcnZlcnUgamXFoXTEmyBqc291L3XFviBuZWpzb3UgZGFsxaHDrSB6cHLDoXZ5XHJcbiAqL1xyXG52YXIgcHJlcGVuZERhdGFJbnRvTWVzc2FnZXMgPSBmdW5jdGlvbih1c2VyQ29kZWRJZCwganNvbkRhdGEsIHVzdWFsTWVzc2FnZXNDb3VudCl7XHJcbiAgdmFyIHRoZXJlSXNNb3JlID0gdHJ1ZTtcclxuICB2YXIgcmVzdWx0ID0ganNvbkRhdGFbdXNlckNvZGVkSWRdO1xyXG4gIGlmKHJlc3VsdC5tZXNzYWdlcy5sZW5ndGggPCB1c3VhbE1lc3NhZ2VzQ291bnQpey8qIHBva3VkIG3DoW0gbcOpbsSbIHpwcsOhdiBuZcW+IGplIG9idnlrbMOpKi9cclxuICAgIHRoZXJlSXNNb3JlID0gZmFsc2U7XHJcbiAgfWVsc2V7XHJcbiAgICByZXN1bHQubWVzc2FnZXMuc2hpZnQoKTsvKiBvZGViZXJ1IHBydm7DrSB6cHLDoXZ1ICovXHJcbiAgfVxyXG4gIF90aGVyZUlzTW9yZSA9IHRoZXJlSXNNb3JlO1xyXG4gIHZhciB0ZXh0TWVzc2FnZXMgPSBmaWx0ZXJJbmZvTWVzc2FnZXMocmVzdWx0Lm1lc3NhZ2VzKVxyXG4gIHJlc3VsdC5tZXNzYWdlcyA9IG1vZGlmeU1lc3NhZ2VzKHRleHRNZXNzYWdlcyk7XHJcbiAgX21lc3NhZ2VzID0gcmVzdWx0Lm1lc3NhZ2VzLmNvbmNhdChfbWVzc2FnZXMpO1xyXG59O1xyXG5cclxuLyoqXHJcbiAqIE9kZmlsdHJ1amUgeiBkYXQgaW5mb3pwcsOhdnkgYSB2eXTFmcOtZMOtIGplIHp2bMOhxaHFpSBkbyBnbG9iw6FsbsOtIHByb23Em25uw6lcclxuICogQHBhcmFtIHtqc29ufSBtZXNzYWdlcyB6cHLDoXZ5IHDFmWlqYXTDqSB6ZSBzZXJ2ZXJ1XHJcbiAqL1xyXG52YXIgZmlsdGVySW5mb01lc3NhZ2VzID0gZnVuY3Rpb24obWVzc2FnZXMpe1xyXG4gIF9pbmZvTWVzc2FnZXMgPSBbXTtcclxuICBmb3IodmFyIGkgPSAwOyBpIDwgbWVzc2FnZXMubGVuZ3RoOyBpKyspe1xyXG4gICAgaWYobWVzc2FnZXNbaV0udHlwZSA9PSAxKXsvKiBrZHnFviBqZSB0byBpbmZvenByw6F2YSAqL1xyXG4gICAgICBhZGRUb0luZm9NZXNzYWdlcyhtZXNzYWdlc1tpXSk7XHJcbiAgICAgIG1lc3NhZ2VzLnNwbGljZShpLDEpOy8qIG9kc3RyYW7Em27DrSB6cHLDoXZ5ICovXHJcbiAgICB9XHJcbiAgfVxyXG4gIHJldHVybiBtZXNzYWdlcztcclxufTtcclxuXHJcbi8qKlxyXG4gKiBQxZlpZMOhIHpwcsOhdnUgayBpbmZvenByw6F2w6FtLCBwb2t1ZCBtZXppIG5pbWkgamXFoXTEmyBuZW7DrVxyXG4gKiBAcGFyYW0gIHtqc29ufSBtZXNzYWdlIHpwcsOhdmEgcMWZaWphdMOhIHplIHNlcnZlcnVcclxuICovXHJcbnZhciBhZGRUb0luZm9NZXNzYWdlcyA9IGZ1bmN0aW9uKG1lc3NhZ2UpIHtcclxuICB2YXIgYWxyZWFkeUV4aXN0cyA9IGZhbHNlO1xyXG4gIF9pbmZvTWVzc2FnZXMuZm9yRWFjaChmdW5jdGlvbihpbmZvTWVzc2FnZSl7XHJcbiAgICBpZihpbmZvTWVzc2FnZS50ZXh0ID09IG1lc3NhZ2UudGV4dCl7XHJcbiAgICAgIGFscmVhZHlFeGlzdHMgPSB0cnVlO1xyXG4gICAgICByZXR1cm47XHJcbiAgICB9XHJcbiAgfSk7XHJcbiAgaWYoIWFscmVhZHlFeGlzdHMpe1xyXG4gICAgX2luZm9NZXNzYWdlcy5wdXNoKG1lc3NhZ2UpO1xyXG4gIH1cclxuICB9O1xyXG4gIC8qKlxyXG4gICAqIE1vZGlmaWt1amUgdGV4dCBkYW7DvWNoIHpwcsOhdiAoc2VtIHBhdMWZw60gemVqbcOpbmEgbmFocmF6b3bDoW7DrSB1csSNaXTDvWNoIMSNw6FzdMOtIG9icsOhemtlbSAtIHNtYWpsw61reSwgZmFja3ksIHBvc2xhbsOpIHVybCBvYnLDoXprdS4uLilcclxuICAgKiBAcGFyYW0gIHtPYmplY3R9IG1lc3NhZ2VzIHNhZGEgenByw6F2XHJcbiAgICovXHJcbiAgdmFyIG1vZGlmeU1lc3NhZ2VzID0gZnVuY3Rpb24obWVzc2FnZXMpIHtcclxuICAgIG1lc3NhZ2VzLmZvckVhY2goZnVuY3Rpb24obWVzc2FnZSl7XHJcbiAgICAgIG1lc3NhZ2UuaW1hZ2VzID0gW107XHJcbiAgICAgIC8qIG5haHJhemVuw60gc3BlY2nDoWxuw61obyBzeW1ib2x1IG9icsOhemtlbSAqL1xyXG4gICAgICBjaGVja1NsYXAobWVzc2FnZSk7XHJcbiAgICB9KTtcclxuICAgIHJldHVybiBtZXNzYWdlcztcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBaa29udHJvbHVqZSwgemRhIHpwcsOhdmEgbmVvYnNhaHVqZSBzeW1ib2wgZmFja3lcclxuICAgKiBAcGFyYW0gIHtPYmplY3R9IG1lc3NhZ2Ugb2JqZWt0IGplZG7DqSB6cHLDoXZ5XHJcbiAgICovXHJcbiAgdmFyIGNoZWNrU2xhcCA9IGZ1bmN0aW9uKG1lc3NhZ2Upe1xyXG4gICAgaWYgKG1lc3NhZ2UudGV4dC5pbmRleE9mKE1lc3NhZ2VDb25zdGFudHMuU0VORF9TTEFQKSA+PSAwKXsvKiBvYnNhaHVqZSBzeW1ib2wgZmFja3kgKi9cclxuICAgICAgbWVzc2FnZS5pbWFnZXMucHVzaCh7LyogcMWZaWTDoW7DrSBmYWNreSBkbyBwb2xlIG9icsOhemvFryAqL1xyXG4gICAgICAgIHVybDogJy4uL2ltYWdlcy9jaGF0Q29udGVudC9zbGFwLWltYWdlLnBuZycsXHJcbiAgICAgICAgd2lkdGg6ICcyNTYnXHJcbiAgICAgIH0pO1xyXG4gICAgICBtZXNzYWdlLnRleHQgPSBtZXNzYWdlLnRleHQucmVwbGFjZShuZXcgUmVnRXhwKE1lc3NhZ2VDb25zdGFudHMuU0VORF9TTEFQLCAnZycpLCAnJyk7Lyogc21hesOhbsOtIHbFoWVjaCBzdHJpbmfFryBwcm8gZmFja3UgKi9cclxuICAgIH1cclxuICB9XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IE1lc3NhZ2VTdG9yZTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcblxyXG4vKioqKioqKioqKiogIElOSUNJQUxJWkFDRSAgKioqKioqKioqKiovXHJcbnZhciBjaGF0Um9vdCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRXaW5kb3cnKTtcclxuaWYodHlwZW9mKGNoYXRSb290KSAhPSAndW5kZWZpbmVkJyAmJiBjaGF0Um9vdCAhPSBudWxsKXsvKmV4aXN0dWplIGVsZW1lbnQgcHJvIGNoYXQqL1xyXG4gIHZhciBDaGF0ID0gcmVxdWlyZSgnLi9jaGF0L3JlYWN0Q2hhdCcpO1xyXG4gIHZhciBsb2dnZWRVc2VyID0ge1xyXG4gICAgbmFtZTogY2hhdFJvb3QuZGF0YXNldC51c2VybmFtZSxcclxuICAgIGFsbG93ZWRUb1NsYXA6IChjaGF0Um9vdC5kYXRhc2V0LmNhbnNsYXAgPT0gJ3RydWUnKSxcclxuICAgIGhyZWY6IGNoYXRSb290LmRhdGFzZXQudXNlcmhyZWYsXHJcbiAgICBwcm9maWxlUGhvdG9Vcmw6IGNoYXRSb290LmRhdGFzZXQucHJvZmlsZXBob3RvdXJsXHJcbiAgfTtcclxuICBSZWFjdC5yZW5kZXIoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoQ2hhdC5DaGF0V2luZG93LCB7dXNlckNvZGVkSWQ6IGNoYXRSb290LmRhdGFzZXQudXNlcmluY2hhdGNvZGVkaWQsIGxvZ2dlZFVzZXI6IGxvZ2dlZFVzZXJ9KSxcclxuICAgICAgY2hhdFJvb3RcclxuICApO1xyXG59XHJcbiJdfQ==
