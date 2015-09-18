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
      slapButton = React.createElement("button", {title: "Poslat facku", className: "sendSlap", onClick: this.sendSlap})
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
  sendSlap: function(){
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwibm9kZV9tb2R1bGVzL2ZsdXgvaW5kZXguanMiLCJub2RlX21vZHVsZXMvZmx1eC9saWIvRGlzcGF0Y2hlci5qcyIsIm5vZGVfbW9kdWxlcy9mbHV4L2xpYi9pbnZhcmlhbnQuanMiLCJub2RlX21vZHVsZXMva2V5bWlycm9yL2luZGV4LmpzIiwibm9kZV9tb2R1bGVzL29iamVjdC1hc3NpZ24vaW5kZXguanMiLCJub2RlX21vZHVsZXMvd2F0Y2hpZnkvbm9kZV9tb2R1bGVzL2Jyb3dzZXJpZnkvbm9kZV9tb2R1bGVzL2V2ZW50cy9ldmVudHMuanMiLCJzcmMvY2hhdC9yZWFjdENoYXQuanMiLCJzcmMvY29tcG9uZW50cy9wcm9maWxlLmpzIiwic3JjL2NvbXBvbmVudHMvdGltZXIuanMiLCJzcmMvZmx1eC9hY3Rpb25zL2NoYXQvTWVzc2FnZUFjdGlvbkNyZWF0b3JzLmpzIiwic3JjL2ZsdXgvY29uc3RhbnRzL0FjdGlvbkNvbnN0YW50cy5qcyIsInNyYy9mbHV4L2NvbnN0YW50cy9DaGF0Q29uc3RhbnRzLmpzIiwic3JjL2ZsdXgvZGlzcGF0Y2hlci9kYXRlbm9kZURpc3BhdGNoZXIuanMiLCJzcmMvZmx1eC9zdG9yZXMvY2hhdC9NZXNzYWdlU3RvcmUuanMiLCJzcmMvcmVhY3REYXRlbm9kZS5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDVkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxUEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDN1NBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwTkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbERBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3ZMQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNkQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcktBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiLyoqXHJcbiAqIENvcHlyaWdodCAoYykgMjAxNC0yMDE1LCBGYWNlYm9vaywgSW5jLlxyXG4gKiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKlxyXG4gKiBUaGlzIHNvdXJjZSBjb2RlIGlzIGxpY2Vuc2VkIHVuZGVyIHRoZSBCU0Qtc3R5bGUgbGljZW5zZSBmb3VuZCBpbiB0aGVcclxuICogTElDRU5TRSBmaWxlIGluIHRoZSByb290IGRpcmVjdG9yeSBvZiB0aGlzIHNvdXJjZSB0cmVlLiBBbiBhZGRpdGlvbmFsIGdyYW50XHJcbiAqIG9mIHBhdGVudCByaWdodHMgY2FuIGJlIGZvdW5kIGluIHRoZSBQQVRFTlRTIGZpbGUgaW4gdGhlIHNhbWUgZGlyZWN0b3J5LlxyXG4gKi9cclxuXHJcbm1vZHVsZS5leHBvcnRzLkRpc3BhdGNoZXIgPSByZXF1aXJlKCcuL2xpYi9EaXNwYXRjaGVyJylcclxuIiwiLypcclxuICogQ29weXJpZ2h0IChjKSAyMDE0LCBGYWNlYm9vaywgSW5jLlxyXG4gKiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKlxyXG4gKiBUaGlzIHNvdXJjZSBjb2RlIGlzIGxpY2Vuc2VkIHVuZGVyIHRoZSBCU0Qtc3R5bGUgbGljZW5zZSBmb3VuZCBpbiB0aGVcclxuICogTElDRU5TRSBmaWxlIGluIHRoZSByb290IGRpcmVjdG9yeSBvZiB0aGlzIHNvdXJjZSB0cmVlLiBBbiBhZGRpdGlvbmFsIGdyYW50XHJcbiAqIG9mIHBhdGVudCByaWdodHMgY2FuIGJlIGZvdW5kIGluIHRoZSBQQVRFTlRTIGZpbGUgaW4gdGhlIHNhbWUgZGlyZWN0b3J5LlxyXG4gKlxyXG4gKiBAcHJvdmlkZXNNb2R1bGUgRGlzcGF0Y2hlclxyXG4gKiBAdHlwZWNoZWNrc1xyXG4gKi9cclxuXHJcblwidXNlIHN0cmljdFwiO1xyXG5cclxudmFyIGludmFyaWFudCA9IHJlcXVpcmUoJy4vaW52YXJpYW50Jyk7XHJcblxyXG52YXIgX2xhc3RJRCA9IDE7XHJcbnZhciBfcHJlZml4ID0gJ0lEXyc7XHJcblxyXG4vKipcclxuICogRGlzcGF0Y2hlciBpcyB1c2VkIHRvIGJyb2FkY2FzdCBwYXlsb2FkcyB0byByZWdpc3RlcmVkIGNhbGxiYWNrcy4gVGhpcyBpc1xyXG4gKiBkaWZmZXJlbnQgZnJvbSBnZW5lcmljIHB1Yi1zdWIgc3lzdGVtcyBpbiB0d28gd2F5czpcclxuICpcclxuICogICAxKSBDYWxsYmFja3MgYXJlIG5vdCBzdWJzY3JpYmVkIHRvIHBhcnRpY3VsYXIgZXZlbnRzLiBFdmVyeSBwYXlsb2FkIGlzXHJcbiAqICAgICAgZGlzcGF0Y2hlZCB0byBldmVyeSByZWdpc3RlcmVkIGNhbGxiYWNrLlxyXG4gKiAgIDIpIENhbGxiYWNrcyBjYW4gYmUgZGVmZXJyZWQgaW4gd2hvbGUgb3IgcGFydCB1bnRpbCBvdGhlciBjYWxsYmFja3MgaGF2ZVxyXG4gKiAgICAgIGJlZW4gZXhlY3V0ZWQuXHJcbiAqXHJcbiAqIEZvciBleGFtcGxlLCBjb25zaWRlciB0aGlzIGh5cG90aGV0aWNhbCBmbGlnaHQgZGVzdGluYXRpb24gZm9ybSwgd2hpY2hcclxuICogc2VsZWN0cyBhIGRlZmF1bHQgY2l0eSB3aGVuIGEgY291bnRyeSBpcyBzZWxlY3RlZDpcclxuICpcclxuICogICB2YXIgZmxpZ2h0RGlzcGF0Y2hlciA9IG5ldyBEaXNwYXRjaGVyKCk7XHJcbiAqXHJcbiAqICAgLy8gS2VlcHMgdHJhY2sgb2Ygd2hpY2ggY291bnRyeSBpcyBzZWxlY3RlZFxyXG4gKiAgIHZhciBDb3VudHJ5U3RvcmUgPSB7Y291bnRyeTogbnVsbH07XHJcbiAqXHJcbiAqICAgLy8gS2VlcHMgdHJhY2sgb2Ygd2hpY2ggY2l0eSBpcyBzZWxlY3RlZFxyXG4gKiAgIHZhciBDaXR5U3RvcmUgPSB7Y2l0eTogbnVsbH07XHJcbiAqXHJcbiAqICAgLy8gS2VlcHMgdHJhY2sgb2YgdGhlIGJhc2UgZmxpZ2h0IHByaWNlIG9mIHRoZSBzZWxlY3RlZCBjaXR5XHJcbiAqICAgdmFyIEZsaWdodFByaWNlU3RvcmUgPSB7cHJpY2U6IG51bGx9XHJcbiAqXHJcbiAqIFdoZW4gYSB1c2VyIGNoYW5nZXMgdGhlIHNlbGVjdGVkIGNpdHksIHdlIGRpc3BhdGNoIHRoZSBwYXlsb2FkOlxyXG4gKlxyXG4gKiAgIGZsaWdodERpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gKiAgICAgYWN0aW9uVHlwZTogJ2NpdHktdXBkYXRlJyxcclxuICogICAgIHNlbGVjdGVkQ2l0eTogJ3BhcmlzJ1xyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBUaGlzIHBheWxvYWQgaXMgZGlnZXN0ZWQgYnkgYENpdHlTdG9yZWA6XHJcbiAqXHJcbiAqICAgZmxpZ2h0RGlzcGF0Y2hlci5yZWdpc3RlcihmdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAqICAgICBpZiAocGF5bG9hZC5hY3Rpb25UeXBlID09PSAnY2l0eS11cGRhdGUnKSB7XHJcbiAqICAgICAgIENpdHlTdG9yZS5jaXR5ID0gcGF5bG9hZC5zZWxlY3RlZENpdHk7XHJcbiAqICAgICB9XHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFdoZW4gdGhlIHVzZXIgc2VsZWN0cyBhIGNvdW50cnksIHdlIGRpc3BhdGNoIHRoZSBwYXlsb2FkOlxyXG4gKlxyXG4gKiAgIGZsaWdodERpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gKiAgICAgYWN0aW9uVHlwZTogJ2NvdW50cnktdXBkYXRlJyxcclxuICogICAgIHNlbGVjdGVkQ291bnRyeTogJ2F1c3RyYWxpYSdcclxuICogICB9KTtcclxuICpcclxuICogVGhpcyBwYXlsb2FkIGlzIGRpZ2VzdGVkIGJ5IGJvdGggc3RvcmVzOlxyXG4gKlxyXG4gKiAgICBDb3VudHJ5U3RvcmUuZGlzcGF0Y2hUb2tlbiA9IGZsaWdodERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gKiAgICAgaWYgKHBheWxvYWQuYWN0aW9uVHlwZSA9PT0gJ2NvdW50cnktdXBkYXRlJykge1xyXG4gKiAgICAgICBDb3VudHJ5U3RvcmUuY291bnRyeSA9IHBheWxvYWQuc2VsZWN0ZWRDb3VudHJ5O1xyXG4gKiAgICAgfVxyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBXaGVuIHRoZSBjYWxsYmFjayB0byB1cGRhdGUgYENvdW50cnlTdG9yZWAgaXMgcmVnaXN0ZXJlZCwgd2Ugc2F2ZSBhIHJlZmVyZW5jZVxyXG4gKiB0byB0aGUgcmV0dXJuZWQgdG9rZW4uIFVzaW5nIHRoaXMgdG9rZW4gd2l0aCBgd2FpdEZvcigpYCwgd2UgY2FuIGd1YXJhbnRlZVxyXG4gKiB0aGF0IGBDb3VudHJ5U3RvcmVgIGlzIHVwZGF0ZWQgYmVmb3JlIHRoZSBjYWxsYmFjayB0aGF0IHVwZGF0ZXMgYENpdHlTdG9yZWBcclxuICogbmVlZHMgdG8gcXVlcnkgaXRzIGRhdGEuXHJcbiAqXHJcbiAqICAgQ2l0eVN0b3JlLmRpc3BhdGNoVG9rZW4gPSBmbGlnaHREaXNwYXRjaGVyLnJlZ2lzdGVyKGZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICogICAgIGlmIChwYXlsb2FkLmFjdGlvblR5cGUgPT09ICdjb3VudHJ5LXVwZGF0ZScpIHtcclxuICogICAgICAgLy8gYENvdW50cnlTdG9yZS5jb3VudHJ5YCBtYXkgbm90IGJlIHVwZGF0ZWQuXHJcbiAqICAgICAgIGZsaWdodERpc3BhdGNoZXIud2FpdEZvcihbQ291bnRyeVN0b3JlLmRpc3BhdGNoVG9rZW5dKTtcclxuICogICAgICAgLy8gYENvdW50cnlTdG9yZS5jb3VudHJ5YCBpcyBub3cgZ3VhcmFudGVlZCB0byBiZSB1cGRhdGVkLlxyXG4gKlxyXG4gKiAgICAgICAvLyBTZWxlY3QgdGhlIGRlZmF1bHQgY2l0eSBmb3IgdGhlIG5ldyBjb3VudHJ5XHJcbiAqICAgICAgIENpdHlTdG9yZS5jaXR5ID0gZ2V0RGVmYXVsdENpdHlGb3JDb3VudHJ5KENvdW50cnlTdG9yZS5jb3VudHJ5KTtcclxuICogICAgIH1cclxuICogICB9KTtcclxuICpcclxuICogVGhlIHVzYWdlIG9mIGB3YWl0Rm9yKClgIGNhbiBiZSBjaGFpbmVkLCBmb3IgZXhhbXBsZTpcclxuICpcclxuICogICBGbGlnaHRQcmljZVN0b3JlLmRpc3BhdGNoVG9rZW4gPVxyXG4gKiAgICAgZmxpZ2h0RGlzcGF0Y2hlci5yZWdpc3RlcihmdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAqICAgICAgIHN3aXRjaCAocGF5bG9hZC5hY3Rpb25UeXBlKSB7XHJcbiAqICAgICAgICAgY2FzZSAnY291bnRyeS11cGRhdGUnOlxyXG4gKiAgICAgICAgICAgZmxpZ2h0RGlzcGF0Y2hlci53YWl0Rm9yKFtDaXR5U3RvcmUuZGlzcGF0Y2hUb2tlbl0pO1xyXG4gKiAgICAgICAgICAgRmxpZ2h0UHJpY2VTdG9yZS5wcmljZSA9XHJcbiAqICAgICAgICAgICAgIGdldEZsaWdodFByaWNlU3RvcmUoQ291bnRyeVN0b3JlLmNvdW50cnksIENpdHlTdG9yZS5jaXR5KTtcclxuICogICAgICAgICAgIGJyZWFrO1xyXG4gKlxyXG4gKiAgICAgICAgIGNhc2UgJ2NpdHktdXBkYXRlJzpcclxuICogICAgICAgICAgIEZsaWdodFByaWNlU3RvcmUucHJpY2UgPVxyXG4gKiAgICAgICAgICAgICBGbGlnaHRQcmljZVN0b3JlKENvdW50cnlTdG9yZS5jb3VudHJ5LCBDaXR5U3RvcmUuY2l0eSk7XHJcbiAqICAgICAgICAgICBicmVhaztcclxuICogICAgIH1cclxuICogICB9KTtcclxuICpcclxuICogVGhlIGBjb3VudHJ5LXVwZGF0ZWAgcGF5bG9hZCB3aWxsIGJlIGd1YXJhbnRlZWQgdG8gaW52b2tlIHRoZSBzdG9yZXMnXHJcbiAqIHJlZ2lzdGVyZWQgY2FsbGJhY2tzIGluIG9yZGVyOiBgQ291bnRyeVN0b3JlYCwgYENpdHlTdG9yZWAsIHRoZW5cclxuICogYEZsaWdodFByaWNlU3RvcmVgLlxyXG4gKi9cclxuXHJcbiAgZnVuY3Rpb24gRGlzcGF0Y2hlcigpIHtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzID0ge307XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzUGVuZGluZyA9IHt9O1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0hhbmRsZWQgPSB7fTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyA9IGZhbHNlO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9wZW5kaW5nUGF5bG9hZCA9IG51bGw7XHJcbiAgfVxyXG5cclxuICAvKipcclxuICAgKiBSZWdpc3RlcnMgYSBjYWxsYmFjayB0byBiZSBpbnZva2VkIHdpdGggZXZlcnkgZGlzcGF0Y2hlZCBwYXlsb2FkLiBSZXR1cm5zXHJcbiAgICogYSB0b2tlbiB0aGF0IGNhbiBiZSB1c2VkIHdpdGggYHdhaXRGb3IoKWAuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge2Z1bmN0aW9ufSBjYWxsYmFja1xyXG4gICAqIEByZXR1cm4ge3N0cmluZ31cclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS5yZWdpc3Rlcj1mdW5jdGlvbihjYWxsYmFjaykge1xyXG4gICAgdmFyIGlkID0gX3ByZWZpeCArIF9sYXN0SUQrKztcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXSA9IGNhbGxiYWNrO1xyXG4gICAgcmV0dXJuIGlkO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIFJlbW92ZXMgYSBjYWxsYmFjayBiYXNlZCBvbiBpdHMgdG9rZW4uXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge3N0cmluZ30gaWRcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS51bnJlZ2lzdGVyPWZ1bmN0aW9uKGlkKSB7XHJcbiAgICBpbnZhcmlhbnQoXHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXSxcclxuICAgICAgJ0Rpc3BhdGNoZXIudW5yZWdpc3RlciguLi4pOiBgJXNgIGRvZXMgbm90IG1hcCB0byBhIHJlZ2lzdGVyZWQgY2FsbGJhY2suJyxcclxuICAgICAgaWRcclxuICAgICk7XHJcbiAgICBkZWxldGUgdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3NbaWRdO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIFdhaXRzIGZvciB0aGUgY2FsbGJhY2tzIHNwZWNpZmllZCB0byBiZSBpbnZva2VkIGJlZm9yZSBjb250aW51aW5nIGV4ZWN1dGlvblxyXG4gICAqIG9mIHRoZSBjdXJyZW50IGNhbGxiYWNrLiBUaGlzIG1ldGhvZCBzaG91bGQgb25seSBiZSB1c2VkIGJ5IGEgY2FsbGJhY2sgaW5cclxuICAgKiByZXNwb25zZSB0byBhIGRpc3BhdGNoZWQgcGF5bG9hZC5cclxuICAgKlxyXG4gICAqIEBwYXJhbSB7YXJyYXk8c3RyaW5nPn0gaWRzXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUud2FpdEZvcj1mdW5jdGlvbihpZHMpIHtcclxuICAgIGludmFyaWFudChcclxuICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nLFxyXG4gICAgICAnRGlzcGF0Y2hlci53YWl0Rm9yKC4uLik6IE11c3QgYmUgaW52b2tlZCB3aGlsZSBkaXNwYXRjaGluZy4nXHJcbiAgICApO1xyXG4gICAgZm9yICh2YXIgaWkgPSAwOyBpaSA8IGlkcy5sZW5ndGg7IGlpKyspIHtcclxuICAgICAgdmFyIGlkID0gaWRzW2lpXTtcclxuICAgICAgaWYgKHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nW2lkXSkge1xyXG4gICAgICAgIGludmFyaWFudChcclxuICAgICAgICAgIHRoaXMuJERpc3BhdGNoZXJfaXNIYW5kbGVkW2lkXSxcclxuICAgICAgICAgICdEaXNwYXRjaGVyLndhaXRGb3IoLi4uKTogQ2lyY3VsYXIgZGVwZW5kZW5jeSBkZXRlY3RlZCB3aGlsZSAnICtcclxuICAgICAgICAgICd3YWl0aW5nIGZvciBgJXNgLicsXHJcbiAgICAgICAgICBpZFxyXG4gICAgICAgICk7XHJcbiAgICAgICAgY29udGludWU7XHJcbiAgICAgIH1cclxuICAgICAgaW52YXJpYW50KFxyXG4gICAgICAgIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXSxcclxuICAgICAgICAnRGlzcGF0Y2hlci53YWl0Rm9yKC4uLik6IGAlc2AgZG9lcyBub3QgbWFwIHRvIGEgcmVnaXN0ZXJlZCBjYWxsYmFjay4nLFxyXG4gICAgICAgIGlkXHJcbiAgICAgICk7XHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfaW52b2tlQ2FsbGJhY2soaWQpO1xyXG4gICAgfVxyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIERpc3BhdGNoZXMgYSBwYXlsb2FkIHRvIGFsbCByZWdpc3RlcmVkIGNhbGxiYWNrcy5cclxuICAgKlxyXG4gICAqIEBwYXJhbSB7b2JqZWN0fSBwYXlsb2FkXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUuZGlzcGF0Y2g9ZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gICAgaW52YXJpYW50KFxyXG4gICAgICAhdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nLFxyXG4gICAgICAnRGlzcGF0Y2guZGlzcGF0Y2goLi4uKTogQ2Fubm90IGRpc3BhdGNoIGluIHRoZSBtaWRkbGUgb2YgYSBkaXNwYXRjaC4nXHJcbiAgICApO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9zdGFydERpc3BhdGNoaW5nKHBheWxvYWQpO1xyXG4gICAgdHJ5IHtcclxuICAgICAgZm9yICh2YXIgaWQgaW4gdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3MpIHtcclxuICAgICAgICBpZiAodGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmdbaWRdKSB7XHJcbiAgICAgICAgICBjb250aW51ZTtcclxuICAgICAgICB9XHJcbiAgICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9pbnZva2VDYWxsYmFjayhpZCk7XHJcbiAgICAgIH1cclxuICAgIH0gZmluYWxseSB7XHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfc3RvcERpc3BhdGNoaW5nKCk7XHJcbiAgICB9XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogSXMgdGhpcyBEaXNwYXRjaGVyIGN1cnJlbnRseSBkaXNwYXRjaGluZy5cclxuICAgKlxyXG4gICAqIEByZXR1cm4ge2Jvb2xlYW59XHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUuaXNEaXNwYXRjaGluZz1mdW5jdGlvbigpIHtcclxuICAgIHJldHVybiB0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmc7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogQ2FsbCB0aGUgY2FsbGJhY2sgc3RvcmVkIHdpdGggdGhlIGdpdmVuIGlkLiBBbHNvIGRvIHNvbWUgaW50ZXJuYWxcclxuICAgKiBib29ra2VlcGluZy5cclxuICAgKlxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBpZFxyXG4gICAqIEBpbnRlcm5hbFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLiREaXNwYXRjaGVyX2ludm9rZUNhbGxiYWNrPWZ1bmN0aW9uKGlkKSB7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzUGVuZGluZ1tpZF0gPSB0cnVlO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3NbaWRdKHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQpO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0hhbmRsZWRbaWRdID0gdHJ1ZTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBTZXQgdXAgYm9va2tlZXBpbmcgbmVlZGVkIHdoZW4gZGlzcGF0Y2hpbmcuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge29iamVjdH0gcGF5bG9hZFxyXG4gICAqIEBpbnRlcm5hbFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLiREaXNwYXRjaGVyX3N0YXJ0RGlzcGF0Y2hpbmc9ZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gICAgZm9yICh2YXIgaWQgaW4gdGhpcy4kRGlzcGF0Y2hlcl9jYWxsYmFja3MpIHtcclxuICAgICAgdGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmdbaWRdID0gZmFsc2U7XHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfaXNIYW5kbGVkW2lkXSA9IGZhbHNlO1xyXG4gICAgfVxyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9wZW5kaW5nUGF5bG9hZCA9IHBheWxvYWQ7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmcgPSB0cnVlO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIENsZWFyIGJvb2trZWVwaW5nIHVzZWQgZm9yIGRpc3BhdGNoaW5nLlxyXG4gICAqXHJcbiAgICogQGludGVybmFsXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUuJERpc3BhdGNoZXJfc3RvcERpc3BhdGNoaW5nPWZ1bmN0aW9uKCkge1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9wZW5kaW5nUGF5bG9hZCA9IG51bGw7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmcgPSBmYWxzZTtcclxuICB9O1xyXG5cclxuXHJcbm1vZHVsZS5leHBvcnRzID0gRGlzcGF0Y2hlcjtcclxuIiwiLyoqXHJcbiAqIENvcHlyaWdodCAoYykgMjAxNCwgRmFjZWJvb2ssIEluYy5cclxuICogQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICpcclxuICogVGhpcyBzb3VyY2UgY29kZSBpcyBsaWNlbnNlZCB1bmRlciB0aGUgQlNELXN0eWxlIGxpY2Vuc2UgZm91bmQgaW4gdGhlXHJcbiAqIExJQ0VOU0UgZmlsZSBpbiB0aGUgcm9vdCBkaXJlY3Rvcnkgb2YgdGhpcyBzb3VyY2UgdHJlZS4gQW4gYWRkaXRpb25hbCBncmFudFxyXG4gKiBvZiBwYXRlbnQgcmlnaHRzIGNhbiBiZSBmb3VuZCBpbiB0aGUgUEFURU5UUyBmaWxlIGluIHRoZSBzYW1lIGRpcmVjdG9yeS5cclxuICpcclxuICogQHByb3ZpZGVzTW9kdWxlIGludmFyaWFudFxyXG4gKi9cclxuXHJcblwidXNlIHN0cmljdFwiO1xyXG5cclxuLyoqXHJcbiAqIFVzZSBpbnZhcmlhbnQoKSB0byBhc3NlcnQgc3RhdGUgd2hpY2ggeW91ciBwcm9ncmFtIGFzc3VtZXMgdG8gYmUgdHJ1ZS5cclxuICpcclxuICogUHJvdmlkZSBzcHJpbnRmLXN0eWxlIGZvcm1hdCAob25seSAlcyBpcyBzdXBwb3J0ZWQpIGFuZCBhcmd1bWVudHNcclxuICogdG8gcHJvdmlkZSBpbmZvcm1hdGlvbiBhYm91dCB3aGF0IGJyb2tlIGFuZCB3aGF0IHlvdSB3ZXJlXHJcbiAqIGV4cGVjdGluZy5cclxuICpcclxuICogVGhlIGludmFyaWFudCBtZXNzYWdlIHdpbGwgYmUgc3RyaXBwZWQgaW4gcHJvZHVjdGlvbiwgYnV0IHRoZSBpbnZhcmlhbnRcclxuICogd2lsbCByZW1haW4gdG8gZW5zdXJlIGxvZ2ljIGRvZXMgbm90IGRpZmZlciBpbiBwcm9kdWN0aW9uLlxyXG4gKi9cclxuXHJcbnZhciBpbnZhcmlhbnQgPSBmdW5jdGlvbihjb25kaXRpb24sIGZvcm1hdCwgYSwgYiwgYywgZCwgZSwgZikge1xyXG4gIGlmIChmYWxzZSkge1xyXG4gICAgaWYgKGZvcm1hdCA9PT0gdW5kZWZpbmVkKSB7XHJcbiAgICAgIHRocm93IG5ldyBFcnJvcignaW52YXJpYW50IHJlcXVpcmVzIGFuIGVycm9yIG1lc3NhZ2UgYXJndW1lbnQnKTtcclxuICAgIH1cclxuICB9XHJcblxyXG4gIGlmICghY29uZGl0aW9uKSB7XHJcbiAgICB2YXIgZXJyb3I7XHJcbiAgICBpZiAoZm9ybWF0ID09PSB1bmRlZmluZWQpIHtcclxuICAgICAgZXJyb3IgPSBuZXcgRXJyb3IoXHJcbiAgICAgICAgJ01pbmlmaWVkIGV4Y2VwdGlvbiBvY2N1cnJlZDsgdXNlIHRoZSBub24tbWluaWZpZWQgZGV2IGVudmlyb25tZW50ICcgK1xyXG4gICAgICAgICdmb3IgdGhlIGZ1bGwgZXJyb3IgbWVzc2FnZSBhbmQgYWRkaXRpb25hbCBoZWxwZnVsIHdhcm5pbmdzLidcclxuICAgICAgKTtcclxuICAgIH0gZWxzZSB7XHJcbiAgICAgIHZhciBhcmdzID0gW2EsIGIsIGMsIGQsIGUsIGZdO1xyXG4gICAgICB2YXIgYXJnSW5kZXggPSAwO1xyXG4gICAgICBlcnJvciA9IG5ldyBFcnJvcihcclxuICAgICAgICAnSW52YXJpYW50IFZpb2xhdGlvbjogJyArXHJcbiAgICAgICAgZm9ybWF0LnJlcGxhY2UoLyVzL2csIGZ1bmN0aW9uKCkgeyByZXR1cm4gYXJnc1thcmdJbmRleCsrXTsgfSlcclxuICAgICAgKTtcclxuICAgIH1cclxuXHJcbiAgICBlcnJvci5mcmFtZXNUb1BvcCA9IDE7IC8vIHdlIGRvbid0IGNhcmUgYWJvdXQgaW52YXJpYW50J3Mgb3duIGZyYW1lXHJcbiAgICB0aHJvdyBlcnJvcjtcclxuICB9XHJcbn07XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IGludmFyaWFudDtcclxuIiwiLyoqXHJcbiAqIENvcHlyaWdodCAyMDEzLTIwMTQgRmFjZWJvb2ssIEluYy5cclxuICpcclxuICogTGljZW5zZWQgdW5kZXIgdGhlIEFwYWNoZSBMaWNlbnNlLCBWZXJzaW9uIDIuMCAodGhlIFwiTGljZW5zZVwiKTtcclxuICogeW91IG1heSBub3QgdXNlIHRoaXMgZmlsZSBleGNlcHQgaW4gY29tcGxpYW5jZSB3aXRoIHRoZSBMaWNlbnNlLlxyXG4gKiBZb3UgbWF5IG9idGFpbiBhIGNvcHkgb2YgdGhlIExpY2Vuc2UgYXRcclxuICpcclxuICogaHR0cDovL3d3dy5hcGFjaGUub3JnL2xpY2Vuc2VzL0xJQ0VOU0UtMi4wXHJcbiAqXHJcbiAqIFVubGVzcyByZXF1aXJlZCBieSBhcHBsaWNhYmxlIGxhdyBvciBhZ3JlZWQgdG8gaW4gd3JpdGluZywgc29mdHdhcmVcclxuICogZGlzdHJpYnV0ZWQgdW5kZXIgdGhlIExpY2Vuc2UgaXMgZGlzdHJpYnV0ZWQgb24gYW4gXCJBUyBJU1wiIEJBU0lTLFxyXG4gKiBXSVRIT1VUIFdBUlJBTlRJRVMgT1IgQ09ORElUSU9OUyBPRiBBTlkgS0lORCwgZWl0aGVyIGV4cHJlc3Mgb3IgaW1wbGllZC5cclxuICogU2VlIHRoZSBMaWNlbnNlIGZvciB0aGUgc3BlY2lmaWMgbGFuZ3VhZ2UgZ292ZXJuaW5nIHBlcm1pc3Npb25zIGFuZFxyXG4gKiBsaW1pdGF0aW9ucyB1bmRlciB0aGUgTGljZW5zZS5cclxuICpcclxuICovXHJcblxyXG5cInVzZSBzdHJpY3RcIjtcclxuXHJcbi8qKlxyXG4gKiBDb25zdHJ1Y3RzIGFuIGVudW1lcmF0aW9uIHdpdGgga2V5cyBlcXVhbCB0byB0aGVpciB2YWx1ZS5cclxuICpcclxuICogRm9yIGV4YW1wbGU6XHJcbiAqXHJcbiAqICAgdmFyIENPTE9SUyA9IGtleU1pcnJvcih7Ymx1ZTogbnVsbCwgcmVkOiBudWxsfSk7XHJcbiAqICAgdmFyIG15Q29sb3IgPSBDT0xPUlMuYmx1ZTtcclxuICogICB2YXIgaXNDb2xvclZhbGlkID0gISFDT0xPUlNbbXlDb2xvcl07XHJcbiAqXHJcbiAqIFRoZSBsYXN0IGxpbmUgY291bGQgbm90IGJlIHBlcmZvcm1lZCBpZiB0aGUgdmFsdWVzIG9mIHRoZSBnZW5lcmF0ZWQgZW51bSB3ZXJlXHJcbiAqIG5vdCBlcXVhbCB0byB0aGVpciBrZXlzLlxyXG4gKlxyXG4gKiAgIElucHV0OiAge2tleTE6IHZhbDEsIGtleTI6IHZhbDJ9XHJcbiAqICAgT3V0cHV0OiB7a2V5MToga2V5MSwga2V5Mjoga2V5Mn1cclxuICpcclxuICogQHBhcmFtIHtvYmplY3R9IG9ialxyXG4gKiBAcmV0dXJuIHtvYmplY3R9XHJcbiAqL1xyXG52YXIga2V5TWlycm9yID0gZnVuY3Rpb24ob2JqKSB7XHJcbiAgdmFyIHJldCA9IHt9O1xyXG4gIHZhciBrZXk7XHJcbiAgaWYgKCEob2JqIGluc3RhbmNlb2YgT2JqZWN0ICYmICFBcnJheS5pc0FycmF5KG9iaikpKSB7XHJcbiAgICB0aHJvdyBuZXcgRXJyb3IoJ2tleU1pcnJvciguLi4pOiBBcmd1bWVudCBtdXN0IGJlIGFuIG9iamVjdC4nKTtcclxuICB9XHJcbiAgZm9yIChrZXkgaW4gb2JqKSB7XHJcbiAgICBpZiAoIW9iai5oYXNPd25Qcm9wZXJ0eShrZXkpKSB7XHJcbiAgICAgIGNvbnRpbnVlO1xyXG4gICAgfVxyXG4gICAgcmV0W2tleV0gPSBrZXk7XHJcbiAgfVxyXG4gIHJldHVybiByZXQ7XHJcbn07XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IGtleU1pcnJvcjtcclxuIiwiJ3VzZSBzdHJpY3QnO1xyXG5cclxuZnVuY3Rpb24gVG9PYmplY3QodmFsKSB7XHJcblx0aWYgKHZhbCA9PSBudWxsKSB7XHJcblx0XHR0aHJvdyBuZXcgVHlwZUVycm9yKCdPYmplY3QuYXNzaWduIGNhbm5vdCBiZSBjYWxsZWQgd2l0aCBudWxsIG9yIHVuZGVmaW5lZCcpO1xyXG5cdH1cclxuXHJcblx0cmV0dXJuIE9iamVjdCh2YWwpO1xyXG59XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IE9iamVjdC5hc3NpZ24gfHwgZnVuY3Rpb24gKHRhcmdldCwgc291cmNlKSB7XHJcblx0dmFyIHBlbmRpbmdFeGNlcHRpb247XHJcblx0dmFyIGZyb207XHJcblx0dmFyIGtleXM7XHJcblx0dmFyIHRvID0gVG9PYmplY3QodGFyZ2V0KTtcclxuXHJcblx0Zm9yICh2YXIgcyA9IDE7IHMgPCBhcmd1bWVudHMubGVuZ3RoOyBzKyspIHtcclxuXHRcdGZyb20gPSBhcmd1bWVudHNbc107XHJcblx0XHRrZXlzID0gT2JqZWN0LmtleXMoT2JqZWN0KGZyb20pKTtcclxuXHJcblx0XHRmb3IgKHZhciBpID0gMDsgaSA8IGtleXMubGVuZ3RoOyBpKyspIHtcclxuXHRcdFx0dHJ5IHtcclxuXHRcdFx0XHR0b1trZXlzW2ldXSA9IGZyb21ba2V5c1tpXV07XHJcblx0XHRcdH0gY2F0Y2ggKGVycikge1xyXG5cdFx0XHRcdGlmIChwZW5kaW5nRXhjZXB0aW9uID09PSB1bmRlZmluZWQpIHtcclxuXHRcdFx0XHRcdHBlbmRpbmdFeGNlcHRpb24gPSBlcnI7XHJcblx0XHRcdFx0fVxyXG5cdFx0XHR9XHJcblx0XHR9XHJcblx0fVxyXG5cclxuXHRpZiAocGVuZGluZ0V4Y2VwdGlvbikge1xyXG5cdFx0dGhyb3cgcGVuZGluZ0V4Y2VwdGlvbjtcclxuXHR9XHJcblxyXG5cdHJldHVybiB0bztcclxufTtcclxuIiwiLy8gQ29weXJpZ2h0IEpveWVudCwgSW5jLiBhbmQgb3RoZXIgTm9kZSBjb250cmlidXRvcnMuXHJcbi8vXHJcbi8vIFBlcm1pc3Npb24gaXMgaGVyZWJ5IGdyYW50ZWQsIGZyZWUgb2YgY2hhcmdlLCB0byBhbnkgcGVyc29uIG9idGFpbmluZyBhXHJcbi8vIGNvcHkgb2YgdGhpcyBzb2Z0d2FyZSBhbmQgYXNzb2NpYXRlZCBkb2N1bWVudGF0aW9uIGZpbGVzICh0aGVcclxuLy8gXCJTb2Z0d2FyZVwiKSwgdG8gZGVhbCBpbiB0aGUgU29mdHdhcmUgd2l0aG91dCByZXN0cmljdGlvbiwgaW5jbHVkaW5nXHJcbi8vIHdpdGhvdXQgbGltaXRhdGlvbiB0aGUgcmlnaHRzIHRvIHVzZSwgY29weSwgbW9kaWZ5LCBtZXJnZSwgcHVibGlzaCxcclxuLy8gZGlzdHJpYnV0ZSwgc3VibGljZW5zZSwgYW5kL29yIHNlbGwgY29waWVzIG9mIHRoZSBTb2Z0d2FyZSwgYW5kIHRvIHBlcm1pdFxyXG4vLyBwZXJzb25zIHRvIHdob20gdGhlIFNvZnR3YXJlIGlzIGZ1cm5pc2hlZCB0byBkbyBzbywgc3ViamVjdCB0byB0aGVcclxuLy8gZm9sbG93aW5nIGNvbmRpdGlvbnM6XHJcbi8vXHJcbi8vIFRoZSBhYm92ZSBjb3B5cmlnaHQgbm90aWNlIGFuZCB0aGlzIHBlcm1pc3Npb24gbm90aWNlIHNoYWxsIGJlIGluY2x1ZGVkXHJcbi8vIGluIGFsbCBjb3BpZXMgb3Igc3Vic3RhbnRpYWwgcG9ydGlvbnMgb2YgdGhlIFNvZnR3YXJlLlxyXG4vL1xyXG4vLyBUSEUgU09GVFdBUkUgSVMgUFJPVklERUQgXCJBUyBJU1wiLCBXSVRIT1VUIFdBUlJBTlRZIE9GIEFOWSBLSU5ELCBFWFBSRVNTXHJcbi8vIE9SIElNUExJRUQsIElOQ0xVRElORyBCVVQgTk9UIExJTUlURUQgVE8gVEhFIFdBUlJBTlRJRVMgT0ZcclxuLy8gTUVSQ0hBTlRBQklMSVRZLCBGSVRORVNTIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRSBBTkQgTk9OSU5GUklOR0VNRU5ULiBJTlxyXG4vLyBOTyBFVkVOVCBTSEFMTCBUSEUgQVVUSE9SUyBPUiBDT1BZUklHSFQgSE9MREVSUyBCRSBMSUFCTEUgRk9SIEFOWSBDTEFJTSxcclxuLy8gREFNQUdFUyBPUiBPVEhFUiBMSUFCSUxJVFksIFdIRVRIRVIgSU4gQU4gQUNUSU9OIE9GIENPTlRSQUNULCBUT1JUIE9SXHJcbi8vIE9USEVSV0lTRSwgQVJJU0lORyBGUk9NLCBPVVQgT0YgT1IgSU4gQ09OTkVDVElPTiBXSVRIIFRIRSBTT0ZUV0FSRSBPUiBUSEVcclxuLy8gVVNFIE9SIE9USEVSIERFQUxJTkdTIElOIFRIRSBTT0ZUV0FSRS5cclxuXHJcbmZ1bmN0aW9uIEV2ZW50RW1pdHRlcigpIHtcclxuICB0aGlzLl9ldmVudHMgPSB0aGlzLl9ldmVudHMgfHwge307XHJcbiAgdGhpcy5fbWF4TGlzdGVuZXJzID0gdGhpcy5fbWF4TGlzdGVuZXJzIHx8IHVuZGVmaW5lZDtcclxufVxyXG5tb2R1bGUuZXhwb3J0cyA9IEV2ZW50RW1pdHRlcjtcclxuXHJcbi8vIEJhY2t3YXJkcy1jb21wYXQgd2l0aCBub2RlIDAuMTAueFxyXG5FdmVudEVtaXR0ZXIuRXZlbnRFbWl0dGVyID0gRXZlbnRFbWl0dGVyO1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5fZXZlbnRzID0gdW5kZWZpbmVkO1xyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLl9tYXhMaXN0ZW5lcnMgPSB1bmRlZmluZWQ7XHJcblxyXG4vLyBCeSBkZWZhdWx0IEV2ZW50RW1pdHRlcnMgd2lsbCBwcmludCBhIHdhcm5pbmcgaWYgbW9yZSB0aGFuIDEwIGxpc3RlbmVycyBhcmVcclxuLy8gYWRkZWQgdG8gaXQuIFRoaXMgaXMgYSB1c2VmdWwgZGVmYXVsdCB3aGljaCBoZWxwcyBmaW5kaW5nIG1lbW9yeSBsZWFrcy5cclxuRXZlbnRFbWl0dGVyLmRlZmF1bHRNYXhMaXN0ZW5lcnMgPSAxMDtcclxuXHJcbi8vIE9idmlvdXNseSBub3QgYWxsIEVtaXR0ZXJzIHNob3VsZCBiZSBsaW1pdGVkIHRvIDEwLiBUaGlzIGZ1bmN0aW9uIGFsbG93c1xyXG4vLyB0aGF0IHRvIGJlIGluY3JlYXNlZC4gU2V0IHRvIHplcm8gZm9yIHVubGltaXRlZC5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5zZXRNYXhMaXN0ZW5lcnMgPSBmdW5jdGlvbihuKSB7XHJcbiAgaWYgKCFpc051bWJlcihuKSB8fCBuIDwgMCB8fCBpc05hTihuKSlcclxuICAgIHRocm93IFR5cGVFcnJvcignbiBtdXN0IGJlIGEgcG9zaXRpdmUgbnVtYmVyJyk7XHJcbiAgdGhpcy5fbWF4TGlzdGVuZXJzID0gbjtcclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuZW1pdCA9IGZ1bmN0aW9uKHR5cGUpIHtcclxuICB2YXIgZXIsIGhhbmRsZXIsIGxlbiwgYXJncywgaSwgbGlzdGVuZXJzO1xyXG5cclxuICBpZiAoIXRoaXMuX2V2ZW50cylcclxuICAgIHRoaXMuX2V2ZW50cyA9IHt9O1xyXG5cclxuICAvLyBJZiB0aGVyZSBpcyBubyAnZXJyb3InIGV2ZW50IGxpc3RlbmVyIHRoZW4gdGhyb3cuXHJcbiAgaWYgKHR5cGUgPT09ICdlcnJvcicpIHtcclxuICAgIGlmICghdGhpcy5fZXZlbnRzLmVycm9yIHx8XHJcbiAgICAgICAgKGlzT2JqZWN0KHRoaXMuX2V2ZW50cy5lcnJvcikgJiYgIXRoaXMuX2V2ZW50cy5lcnJvci5sZW5ndGgpKSB7XHJcbiAgICAgIGVyID0gYXJndW1lbnRzWzFdO1xyXG4gICAgICBpZiAoZXIgaW5zdGFuY2VvZiBFcnJvcikge1xyXG4gICAgICAgIHRocm93IGVyOyAvLyBVbmhhbmRsZWQgJ2Vycm9yJyBldmVudFxyXG4gICAgICB9XHJcbiAgICAgIHRocm93IFR5cGVFcnJvcignVW5jYXVnaHQsIHVuc3BlY2lmaWVkIFwiZXJyb3JcIiBldmVudC4nKTtcclxuICAgIH1cclxuICB9XHJcblxyXG4gIGhhbmRsZXIgPSB0aGlzLl9ldmVudHNbdHlwZV07XHJcblxyXG4gIGlmIChpc1VuZGVmaW5lZChoYW5kbGVyKSlcclxuICAgIHJldHVybiBmYWxzZTtcclxuXHJcbiAgaWYgKGlzRnVuY3Rpb24oaGFuZGxlcikpIHtcclxuICAgIHN3aXRjaCAoYXJndW1lbnRzLmxlbmd0aCkge1xyXG4gICAgICAvLyBmYXN0IGNhc2VzXHJcbiAgICAgIGNhc2UgMTpcclxuICAgICAgICBoYW5kbGVyLmNhbGwodGhpcyk7XHJcbiAgICAgICAgYnJlYWs7XHJcbiAgICAgIGNhc2UgMjpcclxuICAgICAgICBoYW5kbGVyLmNhbGwodGhpcywgYXJndW1lbnRzWzFdKTtcclxuICAgICAgICBicmVhaztcclxuICAgICAgY2FzZSAzOlxyXG4gICAgICAgIGhhbmRsZXIuY2FsbCh0aGlzLCBhcmd1bWVudHNbMV0sIGFyZ3VtZW50c1syXSk7XHJcbiAgICAgICAgYnJlYWs7XHJcbiAgICAgIC8vIHNsb3dlclxyXG4gICAgICBkZWZhdWx0OlxyXG4gICAgICAgIGxlbiA9IGFyZ3VtZW50cy5sZW5ndGg7XHJcbiAgICAgICAgYXJncyA9IG5ldyBBcnJheShsZW4gLSAxKTtcclxuICAgICAgICBmb3IgKGkgPSAxOyBpIDwgbGVuOyBpKyspXHJcbiAgICAgICAgICBhcmdzW2kgLSAxXSA9IGFyZ3VtZW50c1tpXTtcclxuICAgICAgICBoYW5kbGVyLmFwcGx5KHRoaXMsIGFyZ3MpO1xyXG4gICAgfVxyXG4gIH0gZWxzZSBpZiAoaXNPYmplY3QoaGFuZGxlcikpIHtcclxuICAgIGxlbiA9IGFyZ3VtZW50cy5sZW5ndGg7XHJcbiAgICBhcmdzID0gbmV3IEFycmF5KGxlbiAtIDEpO1xyXG4gICAgZm9yIChpID0gMTsgaSA8IGxlbjsgaSsrKVxyXG4gICAgICBhcmdzW2kgLSAxXSA9IGFyZ3VtZW50c1tpXTtcclxuXHJcbiAgICBsaXN0ZW5lcnMgPSBoYW5kbGVyLnNsaWNlKCk7XHJcbiAgICBsZW4gPSBsaXN0ZW5lcnMubGVuZ3RoO1xyXG4gICAgZm9yIChpID0gMDsgaSA8IGxlbjsgaSsrKVxyXG4gICAgICBsaXN0ZW5lcnNbaV0uYXBwbHkodGhpcywgYXJncyk7XHJcbiAgfVxyXG5cclxuICByZXR1cm4gdHJ1ZTtcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuYWRkTGlzdGVuZXIgPSBmdW5jdGlvbih0eXBlLCBsaXN0ZW5lcikge1xyXG4gIHZhciBtO1xyXG5cclxuICBpZiAoIWlzRnVuY3Rpb24obGlzdGVuZXIpKVxyXG4gICAgdGhyb3cgVHlwZUVycm9yKCdsaXN0ZW5lciBtdXN0IGJlIGEgZnVuY3Rpb24nKTtcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHMpXHJcbiAgICB0aGlzLl9ldmVudHMgPSB7fTtcclxuXHJcbiAgLy8gVG8gYXZvaWQgcmVjdXJzaW9uIGluIHRoZSBjYXNlIHRoYXQgdHlwZSA9PT0gXCJuZXdMaXN0ZW5lclwiISBCZWZvcmVcclxuICAvLyBhZGRpbmcgaXQgdG8gdGhlIGxpc3RlbmVycywgZmlyc3QgZW1pdCBcIm5ld0xpc3RlbmVyXCIuXHJcbiAgaWYgKHRoaXMuX2V2ZW50cy5uZXdMaXN0ZW5lcilcclxuICAgIHRoaXMuZW1pdCgnbmV3TGlzdGVuZXInLCB0eXBlLFxyXG4gICAgICAgICAgICAgIGlzRnVuY3Rpb24obGlzdGVuZXIubGlzdGVuZXIpID9cclxuICAgICAgICAgICAgICBsaXN0ZW5lci5saXN0ZW5lciA6IGxpc3RlbmVyKTtcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHNbdHlwZV0pXHJcbiAgICAvLyBPcHRpbWl6ZSB0aGUgY2FzZSBvZiBvbmUgbGlzdGVuZXIuIERvbid0IG5lZWQgdGhlIGV4dHJhIGFycmF5IG9iamVjdC5cclxuICAgIHRoaXMuX2V2ZW50c1t0eXBlXSA9IGxpc3RlbmVyO1xyXG4gIGVsc2UgaWYgKGlzT2JqZWN0KHRoaXMuX2V2ZW50c1t0eXBlXSkpXHJcbiAgICAvLyBJZiB3ZSd2ZSBhbHJlYWR5IGdvdCBhbiBhcnJheSwganVzdCBhcHBlbmQuXHJcbiAgICB0aGlzLl9ldmVudHNbdHlwZV0ucHVzaChsaXN0ZW5lcik7XHJcbiAgZWxzZVxyXG4gICAgLy8gQWRkaW5nIHRoZSBzZWNvbmQgZWxlbWVudCwgbmVlZCB0byBjaGFuZ2UgdG8gYXJyYXkuXHJcbiAgICB0aGlzLl9ldmVudHNbdHlwZV0gPSBbdGhpcy5fZXZlbnRzW3R5cGVdLCBsaXN0ZW5lcl07XHJcblxyXG4gIC8vIENoZWNrIGZvciBsaXN0ZW5lciBsZWFrXHJcbiAgaWYgKGlzT2JqZWN0KHRoaXMuX2V2ZW50c1t0eXBlXSkgJiYgIXRoaXMuX2V2ZW50c1t0eXBlXS53YXJuZWQpIHtcclxuICAgIHZhciBtO1xyXG4gICAgaWYgKCFpc1VuZGVmaW5lZCh0aGlzLl9tYXhMaXN0ZW5lcnMpKSB7XHJcbiAgICAgIG0gPSB0aGlzLl9tYXhMaXN0ZW5lcnM7XHJcbiAgICB9IGVsc2Uge1xyXG4gICAgICBtID0gRXZlbnRFbWl0dGVyLmRlZmF1bHRNYXhMaXN0ZW5lcnM7XHJcbiAgICB9XHJcblxyXG4gICAgaWYgKG0gJiYgbSA+IDAgJiYgdGhpcy5fZXZlbnRzW3R5cGVdLmxlbmd0aCA+IG0pIHtcclxuICAgICAgdGhpcy5fZXZlbnRzW3R5cGVdLndhcm5lZCA9IHRydWU7XHJcbiAgICAgIGNvbnNvbGUuZXJyb3IoJyhub2RlKSB3YXJuaW5nOiBwb3NzaWJsZSBFdmVudEVtaXR0ZXIgbWVtb3J5ICcgK1xyXG4gICAgICAgICAgICAgICAgICAgICdsZWFrIGRldGVjdGVkLiAlZCBsaXN0ZW5lcnMgYWRkZWQuICcgK1xyXG4gICAgICAgICAgICAgICAgICAgICdVc2UgZW1pdHRlci5zZXRNYXhMaXN0ZW5lcnMoKSB0byBpbmNyZWFzZSBsaW1pdC4nLFxyXG4gICAgICAgICAgICAgICAgICAgIHRoaXMuX2V2ZW50c1t0eXBlXS5sZW5ndGgpO1xyXG4gICAgICBpZiAodHlwZW9mIGNvbnNvbGUudHJhY2UgPT09ICdmdW5jdGlvbicpIHtcclxuICAgICAgICAvLyBub3Qgc3VwcG9ydGVkIGluIElFIDEwXHJcbiAgICAgICAgY29uc29sZS50cmFjZSgpO1xyXG4gICAgICB9XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUub24gPSBFdmVudEVtaXR0ZXIucHJvdG90eXBlLmFkZExpc3RlbmVyO1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5vbmNlID0gZnVuY3Rpb24odHlwZSwgbGlzdGVuZXIpIHtcclxuICBpZiAoIWlzRnVuY3Rpb24obGlzdGVuZXIpKVxyXG4gICAgdGhyb3cgVHlwZUVycm9yKCdsaXN0ZW5lciBtdXN0IGJlIGEgZnVuY3Rpb24nKTtcclxuXHJcbiAgdmFyIGZpcmVkID0gZmFsc2U7XHJcblxyXG4gIGZ1bmN0aW9uIGcoKSB7XHJcbiAgICB0aGlzLnJlbW92ZUxpc3RlbmVyKHR5cGUsIGcpO1xyXG5cclxuICAgIGlmICghZmlyZWQpIHtcclxuICAgICAgZmlyZWQgPSB0cnVlO1xyXG4gICAgICBsaXN0ZW5lci5hcHBseSh0aGlzLCBhcmd1bWVudHMpO1xyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgZy5saXN0ZW5lciA9IGxpc3RlbmVyO1xyXG4gIHRoaXMub24odHlwZSwgZyk7XHJcblxyXG4gIHJldHVybiB0aGlzO1xyXG59O1xyXG5cclxuLy8gZW1pdHMgYSAncmVtb3ZlTGlzdGVuZXInIGV2ZW50IGlmZiB0aGUgbGlzdGVuZXIgd2FzIHJlbW92ZWRcclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5yZW1vdmVMaXN0ZW5lciA9IGZ1bmN0aW9uKHR5cGUsIGxpc3RlbmVyKSB7XHJcbiAgdmFyIGxpc3QsIHBvc2l0aW9uLCBsZW5ndGgsIGk7XHJcblxyXG4gIGlmICghaXNGdW5jdGlvbihsaXN0ZW5lcikpXHJcbiAgICB0aHJvdyBUeXBlRXJyb3IoJ2xpc3RlbmVyIG11c3QgYmUgYSBmdW5jdGlvbicpO1xyXG5cclxuICBpZiAoIXRoaXMuX2V2ZW50cyB8fCAhdGhpcy5fZXZlbnRzW3R5cGVdKVxyXG4gICAgcmV0dXJuIHRoaXM7XHJcblxyXG4gIGxpc3QgPSB0aGlzLl9ldmVudHNbdHlwZV07XHJcbiAgbGVuZ3RoID0gbGlzdC5sZW5ndGg7XHJcbiAgcG9zaXRpb24gPSAtMTtcclxuXHJcbiAgaWYgKGxpc3QgPT09IGxpc3RlbmVyIHx8XHJcbiAgICAgIChpc0Z1bmN0aW9uKGxpc3QubGlzdGVuZXIpICYmIGxpc3QubGlzdGVuZXIgPT09IGxpc3RlbmVyKSkge1xyXG4gICAgZGVsZXRlIHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuICAgIGlmICh0aGlzLl9ldmVudHMucmVtb3ZlTGlzdGVuZXIpXHJcbiAgICAgIHRoaXMuZW1pdCgncmVtb3ZlTGlzdGVuZXInLCB0eXBlLCBsaXN0ZW5lcik7XHJcblxyXG4gIH0gZWxzZSBpZiAoaXNPYmplY3QobGlzdCkpIHtcclxuICAgIGZvciAoaSA9IGxlbmd0aDsgaS0tID4gMDspIHtcclxuICAgICAgaWYgKGxpc3RbaV0gPT09IGxpc3RlbmVyIHx8XHJcbiAgICAgICAgICAobGlzdFtpXS5saXN0ZW5lciAmJiBsaXN0W2ldLmxpc3RlbmVyID09PSBsaXN0ZW5lcikpIHtcclxuICAgICAgICBwb3NpdGlvbiA9IGk7XHJcbiAgICAgICAgYnJlYWs7XHJcbiAgICAgIH1cclxuICAgIH1cclxuXHJcbiAgICBpZiAocG9zaXRpb24gPCAwKVxyXG4gICAgICByZXR1cm4gdGhpcztcclxuXHJcbiAgICBpZiAobGlzdC5sZW5ndGggPT09IDEpIHtcclxuICAgICAgbGlzdC5sZW5ndGggPSAwO1xyXG4gICAgICBkZWxldGUgdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgbGlzdC5zcGxpY2UocG9zaXRpb24sIDEpO1xyXG4gICAgfVxyXG5cclxuICAgIGlmICh0aGlzLl9ldmVudHMucmVtb3ZlTGlzdGVuZXIpXHJcbiAgICAgIHRoaXMuZW1pdCgncmVtb3ZlTGlzdGVuZXInLCB0eXBlLCBsaXN0ZW5lcik7XHJcbiAgfVxyXG5cclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUucmVtb3ZlQWxsTGlzdGVuZXJzID0gZnVuY3Rpb24odHlwZSkge1xyXG4gIHZhciBrZXksIGxpc3RlbmVycztcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHMpXHJcbiAgICByZXR1cm4gdGhpcztcclxuXHJcbiAgLy8gbm90IGxpc3RlbmluZyBmb3IgcmVtb3ZlTGlzdGVuZXIsIG5vIG5lZWQgdG8gZW1pdFxyXG4gIGlmICghdGhpcy5fZXZlbnRzLnJlbW92ZUxpc3RlbmVyKSB7XHJcbiAgICBpZiAoYXJndW1lbnRzLmxlbmd0aCA9PT0gMClcclxuICAgICAgdGhpcy5fZXZlbnRzID0ge307XHJcbiAgICBlbHNlIGlmICh0aGlzLl9ldmVudHNbdHlwZV0pXHJcbiAgICAgIGRlbGV0ZSB0aGlzLl9ldmVudHNbdHlwZV07XHJcbiAgICByZXR1cm4gdGhpcztcclxuICB9XHJcblxyXG4gIC8vIGVtaXQgcmVtb3ZlTGlzdGVuZXIgZm9yIGFsbCBsaXN0ZW5lcnMgb24gYWxsIGV2ZW50c1xyXG4gIGlmIChhcmd1bWVudHMubGVuZ3RoID09PSAwKSB7XHJcbiAgICBmb3IgKGtleSBpbiB0aGlzLl9ldmVudHMpIHtcclxuICAgICAgaWYgKGtleSA9PT0gJ3JlbW92ZUxpc3RlbmVyJykgY29udGludWU7XHJcbiAgICAgIHRoaXMucmVtb3ZlQWxsTGlzdGVuZXJzKGtleSk7XHJcbiAgICB9XHJcbiAgICB0aGlzLnJlbW92ZUFsbExpc3RlbmVycygncmVtb3ZlTGlzdGVuZXInKTtcclxuICAgIHRoaXMuX2V2ZW50cyA9IHt9O1xyXG4gICAgcmV0dXJuIHRoaXM7XHJcbiAgfVxyXG5cclxuICBsaXN0ZW5lcnMgPSB0aGlzLl9ldmVudHNbdHlwZV07XHJcblxyXG4gIGlmIChpc0Z1bmN0aW9uKGxpc3RlbmVycykpIHtcclxuICAgIHRoaXMucmVtb3ZlTGlzdGVuZXIodHlwZSwgbGlzdGVuZXJzKTtcclxuICB9IGVsc2Uge1xyXG4gICAgLy8gTElGTyBvcmRlclxyXG4gICAgd2hpbGUgKGxpc3RlbmVycy5sZW5ndGgpXHJcbiAgICAgIHRoaXMucmVtb3ZlTGlzdGVuZXIodHlwZSwgbGlzdGVuZXJzW2xpc3RlbmVycy5sZW5ndGggLSAxXSk7XHJcbiAgfVxyXG4gIGRlbGV0ZSB0aGlzLl9ldmVudHNbdHlwZV07XHJcblxyXG4gIHJldHVybiB0aGlzO1xyXG59O1xyXG5cclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5saXN0ZW5lcnMgPSBmdW5jdGlvbih0eXBlKSB7XHJcbiAgdmFyIHJldDtcclxuICBpZiAoIXRoaXMuX2V2ZW50cyB8fCAhdGhpcy5fZXZlbnRzW3R5cGVdKVxyXG4gICAgcmV0ID0gW107XHJcbiAgZWxzZSBpZiAoaXNGdW5jdGlvbih0aGlzLl9ldmVudHNbdHlwZV0pKVxyXG4gICAgcmV0ID0gW3RoaXMuX2V2ZW50c1t0eXBlXV07XHJcbiAgZWxzZVxyXG4gICAgcmV0ID0gdGhpcy5fZXZlbnRzW3R5cGVdLnNsaWNlKCk7XHJcbiAgcmV0dXJuIHJldDtcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5saXN0ZW5lckNvdW50ID0gZnVuY3Rpb24oZW1pdHRlciwgdHlwZSkge1xyXG4gIHZhciByZXQ7XHJcbiAgaWYgKCFlbWl0dGVyLl9ldmVudHMgfHwgIWVtaXR0ZXIuX2V2ZW50c1t0eXBlXSlcclxuICAgIHJldCA9IDA7XHJcbiAgZWxzZSBpZiAoaXNGdW5jdGlvbihlbWl0dGVyLl9ldmVudHNbdHlwZV0pKVxyXG4gICAgcmV0ID0gMTtcclxuICBlbHNlXHJcbiAgICByZXQgPSBlbWl0dGVyLl9ldmVudHNbdHlwZV0ubGVuZ3RoO1xyXG4gIHJldHVybiByZXQ7XHJcbn07XHJcblxyXG5mdW5jdGlvbiBpc0Z1bmN0aW9uKGFyZykge1xyXG4gIHJldHVybiB0eXBlb2YgYXJnID09PSAnZnVuY3Rpb24nO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpc051bWJlcihhcmcpIHtcclxuICByZXR1cm4gdHlwZW9mIGFyZyA9PT0gJ251bWJlcic7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIGlzT2JqZWN0KGFyZykge1xyXG4gIHJldHVybiB0eXBlb2YgYXJnID09PSAnb2JqZWN0JyAmJiBhcmcgIT09IG51bGw7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIGlzVW5kZWZpbmVkKGFyZykge1xyXG4gIHJldHVybiBhcmcgPT09IHZvaWQgMDtcclxufVxyXG4iLCIvKlxyXG4gKiBAYXV0aG9yIEphbiBLb3RhbMOtayA8amFuLmtvdGFsaWsucHJvQGdtYWlsLmNvbT5cclxuICogQGNvcHlyaWdodCBDb3B5cmlnaHQgKGMpIDIwMTMtMjAxNSBLdWtyYWwgQ09NUEFOWSBzLnIuby4gICpcclxuICovXHJcblxyXG4vKiBnbG9iYWwgUmVhY3QgKi8vKiBhYnkgTmV0YmVhbnMgbmV2eWhhem92YWwgY2h5Ynkga3bFr2xpIG5lZGVrbGFyb3ZhbsOpIHByb23Em25uw6kgKi9cclxuXHJcbi8qKioqKioqKioqKiAgWsOBVklTTE9TVEkgICoqKioqKioqKioqL1xyXG52YXIgUHJvZmlsZVBob3RvID0gcmVxdWlyZSgnLi4vY29tcG9uZW50cy9wcm9maWxlJykuUHJvZmlsZVBob3RvO1xyXG52YXIgTWVzc2FnZUNvbnN0YW50cyA9IHJlcXVpcmUoJy4uL2ZsdXgvY29uc3RhbnRzL0NoYXRDb25zdGFudHMnKS5NZXNzYWdlQ29uc3RhbnRzO1xyXG52YXIgTWVzc2FnZUFjdGlvbnMgPSByZXF1aXJlKCcuLi9mbHV4L2FjdGlvbnMvY2hhdC9NZXNzYWdlQWN0aW9uQ3JlYXRvcnMnKTtcclxudmFyIE1lc3NhZ2VTdG9yZSA9IHJlcXVpcmUoJy4uL2ZsdXgvc3RvcmVzL2NoYXQvTWVzc2FnZVN0b3JlJyk7XHJcbnZhciBUaW1lckZhY3RvcnkgPSByZXF1aXJlKCcuLi9jb21wb25lbnRzL3RpbWVyJyk7LyogamUgdiBjYWNoaSwgbmVidWRlIHNlIHZ5dHbDocWZZXQgdsOtY2VrcsOhdCAqL1xyXG5cclxuLyoqKioqKioqKioqICBOQVNUQVZFTsONICAqKioqKioqKioqKi9cclxuXHJcbi8qKiBPZGthenkga2Uga29tdW5pa2FjaSAqL1xyXG52YXIgcmVhY3RTZW5kTWVzc2FnZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRTZW5kTWVzc2FnZUxpbmsnKTtcclxudmFyIHJlYWN0UmVmcmVzaE1lc3NhZ2VzID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlYWN0Q2hhdFJlZnJlc2hNZXNzYWdlc0xpbmsnKTtcclxudmFyIHJlYWN0TG9hZE1lc3NhZ2VzID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlYWN0Q2hhdExvYWRNZXNzYWdlc0xpbmsnKTtcclxudmFyIHJlYWN0R2V0T2xkZXJNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRHZXRPbGRlck1lc3NhZ2VzTGluaycpO1xyXG4vKiBrIHBvc2zDoW7DrSB6cHLDoXZ5Ki9cclxudmFyIHJlYWN0U2VuZE1lc3NhZ2VMaW5rID0gcmVhY3RTZW5kTWVzc2FnZS5ocmVmO1xyXG4vKiBrIHByYXZpZGVsbsOpbXUgZG90YXp1IG5hIHpwcsOhdnkgKi9cclxudmFyIHJlYWN0UmVmcmVzaE1lc3NhZ2VzTGluayA9IHJlYWN0UmVmcmVzaE1lc3NhZ2VzLmhyZWY7XHJcbi8qIGsgZG90YXp1IG5hIG5hxI10ZW7DrSB6cHLDoXYsIGtkecW+IG5lbcOhbSB6YXTDrW0gxb7DoWRuw6kgKHR5cGlja3kgcG9zbGVkbsOtIHpwcsOhdnkgbWV6aSB1xb5pdmF0ZWxpKSAqL1xyXG52YXIgcmVhY3RMb2FkTWVzc2FnZXNMaW5rID0gcmVhY3RMb2FkTWVzc2FnZXMuaHJlZjtcclxuLyogayBkb3RhenUgbmEgc3RhcsWhw60genByw6F2eSAqL1xyXG52YXIgcmVhY3RHZXRPbGRlck1lc3NhZ2VzTGluayA9IHJlYWN0R2V0T2xkZXJNZXNzYWdlcy5ocmVmO1xyXG4vKiogcHJlZml4IHDFmWVkIHBhcmFtZXRyeSBkbyB1cmwgKi9cclxudmFyIHBhcmFtZXRlcnNQcmVmaXggPSByZWFjdFNlbmRNZXNzYWdlLmRhdGFzZXQucGFycHJlZml4O1xyXG4vKiogb2J2eWtsw70gcG/EjWV0IHDFmcOtY2hvesOtY2ggenByw6F2IHYgb2Rwb3bEm2RpIHUgcHJhdmlkZWxuw6lobyBhIGluaWNpw6FsbsOtaG8gcG/FvmFkYXZrdSAoYW5lYiBrb2xpayB6cHLDoXYgbWkgcMWZaWpkZSwga2R5xb4gamljaCBqZSBuYSBzZXJ2ZXJ1IGplxaF0xJsgZG9zdCkgKi9cclxudmFyIHVzdWFsT2xkZXJNZXNzYWdlc0NvdW50ID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzLmRhdGFzZXQubWF4bWVzc2FnZXM7XHJcbnZhciB1c3VhbExvYWRNZXNzYWdlc0NvdW50ID0gcmVhY3RMb2FkTWVzc2FnZXMuZGF0YXNldC5tYXhtZXNzYWdlcztcclxuLyogxI1hc292YcSNIHBybyBwcmF2aWRlbG7DqSBwb8W+YWRhdmt5IG5hIHNlcnZlciAqL1xyXG52YXIgVGltZXIgPSBUaW1lckZhY3RvcnkubmV3SW5zdGFuY2UoKTtcclxuXHJcbi8qKioqKioqKioqKiAgREVGSU5JQ0UgICoqKioqKioqKioqL1xyXG4vKiogxIzDoXN0IG9rbmEsIGt0ZXLDoSBtw6Egc3Zpc2zDvSBwb3N1dm7DrWsgLSBvYnNhaHVqZSB6cHLDoXZ5LCB0bGHEjcOtdGtvIHBybyBkb25hxI3DrXTDoW7DrS4uLiAqL1xyXG52YXIgTWVzc2FnZXNXaW5kb3cgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTWVzc2FnZXNXaW5kb3dcIixcclxuICBnZXRJbml0aWFsU3RhdGU6IGZ1bmN0aW9uKCkge1xyXG4gICAgcmV0dXJuIHttZXNzYWdlczogW10sIGluZm9NZXNzYWdlczogW10sIHRoZXJlSXNNb3JlOiB0cnVlLCBocmVmOiAnJyB9O1xyXG4gIH0sXHJcbiAgY29tcG9uZW50RGlkTW91bnQ6IGZ1bmN0aW9uKCkge1xyXG4gICAgdmFyIGNvbXBvbmVudCA9IHRoaXM7XHJcbiAgICBNZXNzYWdlU3RvcmUuYWRkQ2hhbmdlTGlzdGVuZXIoZnVuY3Rpb24oKXtcclxuICAgICAgY29tcG9uZW50LnNldFN0YXRlKE1lc3NhZ2VTdG9yZS5nZXRTdGF0ZSgpKTtcclxuICAgIH0pO1xyXG4gICAgTWVzc2FnZUFjdGlvbnMuY3JlYXRlR2V0SW5pdGlhbE1lc3NhZ2VzKHJlYWN0TG9hZE1lc3NhZ2VzTGluaywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgcGFyYW1ldGVyc1ByZWZpeCwgdXN1YWxMb2FkTWVzc2FnZXNDb3VudCk7XHJcbiAgfSxcclxuICByZW5kZXI6IGZ1bmN0aW9uKCkge1xyXG4gICAgdmFyIG1lc3NhZ2VzID0gdGhpcy5zdGF0ZS5tZXNzYWdlcztcclxuICAgIHZhciBpbmZvTWVzc2FnZXMgPSB0aGlzLnN0YXRlLmluZm9NZXNzYWdlcztcclxuICAgIHZhciBvbGRlc3RJZCA9IHRoaXMuZ2V0T2xkZXN0SWQobWVzc2FnZXMpO1xyXG4gICAgdmFyIHVzZXJDb2RlZElkID0gdGhpcy5wcm9wcy51c2VyQ29kZWRJZDtcclxuICAgIC8qIHNlc3RhdmVuw60gb2RrYXp1IHBybyB0bGHEjcOtdGtvICovXHJcbiAgICB2YXIgbW9yZUJ1dHRvbkxpbmsgPSByZWFjdEdldE9sZGVyTWVzc2FnZXNMaW5rICsgJyYnICsgcGFyYW1ldGVyc1ByZWZpeCArICdsYXN0SWQ9JyArIG9sZGVzdElkICsgJyYnICsgcGFyYW1ldGVyc1ByZWZpeCArICd3aXRoVXNlcklkPScgKyB0aGlzLnByb3BzLnVzZXJDb2RlZElkO1xyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VzV2luZG93XCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KExvYWRNb3JlQnV0dG9uLCB7bG9hZEhyZWY6IG1vcmVCdXR0b25MaW5rLCBvbGRlc3RJZDogb2xkZXN0SWQsIHRoZXJlSXNNb3JlOiB0aGlzLnN0YXRlLnRoZXJlSXNNb3JlLCB1c2VyQ29kZWRJZDogdXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgbWVzc2FnZXMubWFwKGZ1bmN0aW9uKG1lc3NhZ2UsIGkpe1xyXG4gICAgICAgICAgICByZXR1cm4gUmVhY3QuY3JlYXRlRWxlbWVudChNZXNzYWdlLCB7a2V5OiB1c2VyQ29kZWRJZCArICdtZXNzYWdlJyArIGksIG1lc3NhZ2VEYXRhOiBtZXNzYWdlLCB1c2VySHJlZjogbWVzc2FnZS5wcm9maWxlSHJlZiwgcHJvZmlsZVBob3RvVXJsOiBtZXNzYWdlLnByb2ZpbGVQaG90b1VybH0pO1xyXG4gICAgICAgIH0pLCBcclxuICAgICAgICBcclxuICAgICAgICBpbmZvTWVzc2FnZXMubWFwKGZ1bmN0aW9uKG1lc3NhZ2UsIGkpe1xyXG4gICAgICAgICAgICAgIHJldHVybiBSZWFjdC5jcmVhdGVFbGVtZW50KEluZm9NZXNzYWdlLCB7a2V5OiB1c2VyQ29kZWRJZCArICdpbmZvJyArIGksIG1lc3NhZ2VEYXRhOiBtZXNzYWdlfSk7XHJcbiAgICAgICAgICB9KVxyXG4gICAgICAgIFxyXG4gICAgICApXHJcbiAgICApO1xyXG4gIH0sXHJcbiAgZ2V0T2xkZXN0SWQ6IGZ1bmN0aW9uKG1lc3NhZ2VzKXtcclxuICAgIHJldHVybiAobWVzc2FnZXNbMF0pID8gbWVzc2FnZXNbMF0uaWQgOiA5MDA3MTk5MjU0NzQwOTkxOyAvKm5hc3RhdmVuw60gaG9kbm90eSBuZWJvIG1heGltw6FsbsOtIGhvZG5vdHksIGtkecW+IG5lbsOtKi9cclxuICB9XHJcbn0pO1xyXG5cclxudmFyIEluZm9NZXNzYWdlID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkluZm9NZXNzYWdlXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpe1xyXG4gICAgICByZXR1cm4oUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJpbmZvLW1lc3NhZ2VcIn0sIHRoaXMucHJvcHMubWVzc2FnZURhdGEudGV4dCkpO1xyXG4gIH1cclxufSk7XHJcblxyXG4vKiogSmVkbmEgenByw6F2YS4gKi9cclxudmFyIE1lc3NhZ2UgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTWVzc2FnZVwiLFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbWVzc2FnZSA9IHRoaXMucHJvcHMubWVzc2FnZURhdGE7XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZVwifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChQcm9maWxlUGhvdG8sIHtwcm9maWxlTGluazogdGhpcy5wcm9wcy51c2VySHJlZiwgdXNlck5hbWU6IG1lc3NhZ2UubmFtZSwgcHJvZmlsZVBob3RvVXJsOiB0aGlzLnByb3BzLnByb2ZpbGVQaG90b1VybH0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInBcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlVGV4dFwifSwgXHJcbiAgICAgICAgICBtZXNzYWdlLnRleHQsIFxyXG4gICAgICAgICAgbWVzc2FnZS5pbWFnZXMubWFwKGZ1bmN0aW9uKGltYWdlLCBpKXtcclxuICAgICAgICAgICAgICAgIHJldHVybiBSZWFjdC5jcmVhdGVFbGVtZW50KFwiaW1nXCIsIHtzcmM6IGltYWdlLnVybCwgd2lkdGg6IGltYWdlLndpZHRoLCBrZXk6IG1lc3NhZ2UuaWQgKyAnbWVzc2FnZScgKyBpfSk7XHJcbiAgICAgICAgICAgIH0pLCBcclxuICAgICAgICAgIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlRGF0ZXRpbWVcIn0sIG1lc3NhZ2Uuc2VuZGVkRGF0ZSlcclxuICAgICAgICApLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2xlYXJcIn0pXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBEb25hxI3DrXRhY8OtIHRsYcSNw610a28gKi9cclxudmFyIExvYWRNb3JlQnV0dG9uID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkxvYWRNb3JlQnV0dG9uXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIGlmKCF0aGlzLnByb3BzLnRoZXJlSXNNb3JlKXsgcmV0dXJuIG51bGw7fVxyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJsb2FkTW9yZUJ1dHRvbiBidG4tbWFpbiBsb2FkaW5nYnV0dG9uIHVpLWJ0blwiLCBvbkNsaWNrOiB0aGlzLmhhbmRsZUNsaWNrfSwgXG4gICAgICAgIFwiTmHEjcOtc3QgcMWZZWRjaG96w60genByw6F2eVwiXG4gICAgICApXHJcbiAgICApO1xyXG4gIH0sXHJcbiAgaGFuZGxlQ2xpY2s6IGZ1bmN0aW9uKCl7XHJcbiAgICBNZXNzYWdlQWN0aW9ucy5jcmVhdGVHZXRPbGRlck1lc3NhZ2VzKHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmssIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIHRoaXMucHJvcHMub2xkZXN0SWQsIHBhcmFtZXRlcnNQcmVmaXgsIHVzdWFsT2xkZXJNZXNzYWdlc0NvdW50KTtcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqIEZvcm11bMOhxZkgcHJvIG9kZXPDrWzDoW7DrSB6cHLDoXYgKi9cclxudmFyIE5ld01lc3NhZ2VGb3JtID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIk5ld01lc3NhZ2VGb3JtXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBsb2dnZWRVc2VyID0gdGhpcy5wcm9wcy5sb2dnZWRVc2VyO1xyXG4gICAgdmFyIHNsYXBCdXR0b24gPSAnJztcclxuICAgIGNvbnNvbGUubG9nKGxvZ2dlZFVzZXIpO1xyXG4gICAgaWYgKGxvZ2dlZFVzZXIuYWxsb3dlZFRvU2xhcCl7XHJcbiAgICAgIHNsYXBCdXR0b24gPSBSZWFjdC5jcmVhdGVFbGVtZW50KFwiYnV0dG9uXCIsIHt0aXRsZTogXCJQb3NsYXQgZmFja3VcIiwgY2xhc3NOYW1lOiBcInNlbmRTbGFwXCIsIG9uQ2xpY2s6IHRoaXMuc2VuZFNsYXB9KVxyXG4gICAgfVxyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm5ld01lc3NhZ2VcIn0sIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoUHJvZmlsZVBob3RvLCB7cHJvZmlsZUxpbms6IGxvZ2dlZFVzZXIuaHJlZiwgdXNlck5hbWU6IGxvZ2dlZFVzZXIubmFtZSwgcHJvZmlsZVBob3RvVXJsOiBsb2dnZWRVc2VyLnByb2ZpbGVQaG90b1VybH0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImZvcm1cIiwge29uU3VibWl0OiB0aGlzLm9uU3VibWl0fSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUlucHV0Q29udGFpbmVyXCJ9LCBcclxuICAgICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInRleHRcIiwgY2xhc3NOYW1lOiBcIm1lc3NhZ2VJbnB1dFwifSksIFxyXG4gICAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiaW5wdXRJbnRlcmZhY2VcIn0sIFxyXG4gICAgICAgICAgICAgIHNsYXBCdXR0b25cclxuICAgICAgICAgICAgKSwgXHJcbiAgICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJjbGVhclwifSlcclxuICAgICAgICAgICksIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInN1Ym1pdFwiLCBjbGFzc05hbWU6IFwiYnRuLW1haW4gbWVkaXVtIGJ1dHRvblwiLCB2YWx1ZTogXCJPZGVzbGF0XCJ9KVxyXG4gICAgICAgIClcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9LFxyXG4gIHNlbmRTbGFwOiBmdW5jdGlvbigpe1xyXG4gICAgTWVzc2FnZUFjdGlvbnMuY3JlYXRlU2VuZE1lc3NhZ2UocmVhY3RTZW5kTWVzc2FnZUxpbmssIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIE1lc3NhZ2VDb25zdGFudHMuU0VORF9TTEFQLCBnZXRMYXN0SWQoKSk7XHJcbiAgfSxcclxuICBvblN1Ym1pdDogZnVuY3Rpb24oZSl7LyogVmV6bWUgenByw6F2dSB6ZSBzdWJtaXR1IGEgcG/FoWxlIGppLiBUYWvDqSBzbWHFvmUgenByw6F2dSBuYXBzYW5vdSB2IGlucHV0dS4gKi9cclxuICAgIGUucHJldmVudERlZmF1bHQoKTtcclxuICAgIHZhciBpbnB1dCA9IGUudGFyZ2V0LmdldEVsZW1lbnRzQnlDbGFzc05hbWUoJ21lc3NhZ2VJbnB1dCcpWzBdO1xyXG4gICAgdmFyIG1lc3NhZ2UgPSBpbnB1dC52YWx1ZTtcclxuICAgIGlmKG1lc3NhZ2UgPT0gdW5kZWZpbmVkIHx8IG1lc3NhZ2UudHJpbSgpID09ICcnKSByZXR1cm47XHJcbiAgICBpbnB1dC52YWx1ZSA9ICcnO1xyXG4gICAgTWVzc2FnZUFjdGlvbnMuY3JlYXRlU2VuZE1lc3NhZ2UocmVhY3RTZW5kTWVzc2FnZUxpbmssIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIG1lc3NhZ2UsIGdldExhc3RJZCgpKTtcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqXHJcbiAqIGluaWNpYWxpenVqZSDEjWFzb3ZhxI0gcHJhdmlkZWxuxJsgc2UgZG90YXp1asOtY8OtIG5hIG5vdsOpIHpwcsOhdnkgdiB6w6F2aXNsb3N0aSBuYSB0b20sIGphayBzZSBtxJtuw60gZGF0YSB2IE1lc3NhZ2VTdG9yZVxyXG4gKiBAcGFyYW0ge3N0cmluZ30gdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlLCBzZSBrdGVyw71tIHNpIHDDrcWhdVxyXG4gKi9cclxudmFyIGluaXRpYWxpemVDaGF0VGltZXIgPSBmdW5jdGlvbih1c2VyQ29kZWRJZCl7XHJcbiAgTWVzc2FnZVN0b3JlLmFkZENoYW5nZUxpc3RlbmVyKGZ1bmN0aW9uKCl7XHJcbiAgICB2YXIgc3RhdGUgPSBNZXNzYWdlU3RvcmUuZ2V0U3RhdGUoKTtcclxuICAgIGlmKHN0YXRlLmRhdGFWZXJzaW9uID09IDEpey8qIGRhdGEgc2UgcG9wcnbDqSB6bcSbbmlsYSAqL1xyXG4gICAgICBUaW1lci5tYXhpbXVtSW50ZXJ2YWwgPSA2MDAwMDtcclxuICAgICAgVGltZXIuaW5pdGlhbEludGVydmFsID0gMzAwMDtcclxuICAgICAgVGltZXIuaW50ZXJ2YWxJbmNyYXNlID0gMjAwMDtcclxuICAgICAgVGltZXIubGFzdElkID0gZ2V0TGFzdElkKCk7XHJcbiAgICAgIFRpbWVyLnRpY2sgPSBmdW5jdGlvbigpe1xyXG4gICAgICAgIE1lc3NhZ2VBY3Rpb25zLmNyZWF0ZVJlZnJlc2hNZXNzYWdlcyhyZWFjdFJlZnJlc2hNZXNzYWdlc0xpbmssIHVzZXJDb2RlZElkLCBUaW1lci5sYXN0SWQsIHBhcmFtZXRlcnNQcmVmaXgpO1xyXG4gICAgICB9O1xyXG4gICAgICBUaW1lci5zdGFydCgpO1xyXG4gICAgfWVsc2V7Lyoga2R5xb4gc2UgZGF0YSBuZXptxJtuaWxhIHBvcHJ2w6ksIGFsZSB1csSNaXTEmyBzZSB6bcSbbmlsYSAqL1xyXG4gICAgICBUaW1lci5sYXN0SWQgPSBnZXRMYXN0SWQoKTtcclxuICAgICAgVGltZXIucmVzZXRUaW1lKCk7XHJcbiAgICB9XHJcbiAgfSk7XHJcblxyXG59O1xyXG5cclxuLyoqXHJcbiAqIFZyw6F0w60gcG9zbGVkbsOtIHpuw6Ftw6kgaWRcclxuICogQHJldHVybiB7aW50fSBwb3NsZWRuaSB6bsOhbcOpIGlkXHJcbiAqL1xyXG52YXIgZ2V0TGFzdElkID0gZnVuY3Rpb24oKSB7XHJcbiAgdmFyIHN0YXRlID0gTWVzc2FnZVN0b3JlLmdldFN0YXRlKCk7XHJcbiAgaWYoc3RhdGUubWVzc2FnZXMubGVuZ3RoID4gMCl7XHJcbiAgICByZXR1cm4gc3RhdGUubWVzc2FnZXNbc3RhdGUubWVzc2FnZXMubGVuZ3RoIC0gMV0uaWQ7XHJcbiAgfWVsc2V7XHJcbiAgICByZXR1cm4gMDtcclxuICB9XHJcbn1cclxuXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG4gIC8qKiBPa25vIGNlbMOpaG8gY2hhdHUgcyBqZWRuw61tIHXFvml2YXRlbGVtICovXHJcbiAgQ2hhdFdpbmRvdzogUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkNoYXRXaW5kb3dcIixcclxuICAgIGNvbXBvbmVudERpZE1vdW50OiBmdW5jdGlvbigpIHtcclxuICAgICAgaW5pdGlhbGl6ZUNoYXRUaW1lcih0aGlzLnByb3BzLnVzZXJDb2RlZElkKTtcclxuICAgICAgTWVzc2FnZUFjdGlvbnMucmVsb2FkV2luZG93VW5sb2FkKCk7XHJcbiAgICB9LFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbiAoKSB7XHJcbiAgICAgIHJldHVybiAoXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcImNoYXRXaW5kb3dcIn0sIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChNZXNzYWdlc1dpbmRvdywge3VzZXJDb2RlZElkOiB0aGlzLnByb3BzLnVzZXJDb2RlZElkfSksIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChOZXdNZXNzYWdlRm9ybSwge2xvZ2dlZFVzZXI6IHRoaXMucHJvcHMubG9nZ2VkVXNlciwgdXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KVxyXG4gICAgICAgIClcclxuICAgICAgKVxyXG4gICAgfVxyXG4gIH0pXHJcbn07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuXHJcbiAgLyoqIEtvbXBvbmVudGEgbmEgcHJvZmlsb3ZvdSBmb3RrdSAqL1xyXG4gIFByb2ZpbGVQaG90bzogUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIlByb2ZpbGVQaG90b1wiLFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbiAoKSB7XHJcbiAgICAgIHJldHVybiAoXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImFcIiwge2NsYXNzTmFtZTogXCJnZW5lcmF0ZWRQcm9maWxlXCIsIGhyZWY6IHRoaXMucHJvcHMucHJvZmlsZUxpbmssIHRpdGxlOiB0aGlzLnByb3BzLnVzZXJOYW1lfSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiaW1nXCIsIHtzcmM6IHRoaXMucHJvcHMucHJvZmlsZVBob3RvVXJsfSlcclxuICAgICAgICApXHJcbiAgICAgICk7XHJcbiAgICB9XHJcbiAgfSlcclxuXHJcbn07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKiBUxZnDrWRhIHphamnFocWldWrDrWPDrSBwcmF2aWRlbG7DqSB0aWt5XHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcbi8qKi9cclxuLyogVMWZw61kYSB6YWppxaHFpXVqw61jw60gcHJhdmlkZWxuw6kgdGlreSwga3RlcsOpIHNlIG1vaG91IHMga2HFvmTDvW0gdGlrbnV0w61tIHByb2RsdcW+b3ZhdCAqL1xyXG5mdW5jdGlvbiBUaW1lcigpIHtcclxuICAvKlxyXG4gICAgICAhISEgTkVNxJrFh1RFIFRZVE8gUEFSQU1FVFJZIFDFmMONTU8gViBUT01UTyBTT1VCT1JVLCBaTcSaxYdURSBKRSBVIFZBxaDDjSBJTlNUQU5DRSBUSU1FUlUgISEhXHJcbiAgKi9cclxuICB0aGlzLmN1cnJlbnRJbnRlcnZhbCA9IDEwMDA7IC8qIGFrdHXDoWxuw60gxI1la8OhbsOtIG1lemkgdGlreSAqL1xyXG4gIHRoaXMuaW5pdGlhbEludGVydmFsID0gMTAwMDsgLyogcG/EjcOhdGXEjW7DrSBpbnRlcnZhbCAqL1xyXG4gIHRoaXMuaW50ZXJ2YWxJbmNyYXNlID0gMDsvKiB6dsO9xaFlbsOtIGludGVydmFsdSBwbyBrYcW+ZMOpbSB0aWt1ICovXHJcbiAgdGhpcy5tYXhpbXVtSW50ZXJ2YWwgPSAyMDAwMDsvKiBtYXhpbcOhbG7DrSBpbnRlcnZhbCAqL1xyXG4gIHRoaXMucnVubmluZyA9IGZhbHNlOyAvKiBpbmRpa8OhdG9yLCB6ZGEgdGltZXIgYsSbxb7DrSAqL1xyXG4gIHRoaXMudGljayA9IGZ1bmN0aW9uKCl7fTsvKiBmdW5rY2UsIGNvIHNlIHZvbMOhIHDFmWkga2HFvmTDqW0gdGlrdSAqL1xyXG4gIHRoaXMuc3RhcnQgPSBmdW5jdGlvbigpey8qIGZ1bmtjZSwga3RlcsOhIHNwdXN0w60gxI1hc292YcSNICovXHJcbiAgICBpZighdGhpcy5ydW5uaW5nKXtcclxuICAgICAgdGhpcy5ydW5uaW5nID0gdHJ1ZTtcclxuICAgICAgdGhpcy5yZXNldFRpbWUoKTtcclxuICAgICAgdGhpcy5yZWN1cnNpdmUoKTtcclxuICAgIH1cclxuICB9O1xyXG4gIHRoaXMuc3RvcCA9IGZ1bmN0aW9uKCl7LyogZnVua2NlLCBrdGVyw6EgdGltZXIgemFzdGF2w60qL1xyXG4gICAgdGhpcy5ydW5uaW5nID0gZmFsc2U7XHJcbiAgfTtcclxuICB0aGlzLnJlc2V0VGltZSA9IGZ1bmN0aW9uKCl7LyogZnVua2NlLCBrdGVyb3UgdnlyZXNldHVqaSDEjWVrw6Fuw60gbmEgcG/EjcOhdGXEjW7DrSBob2Rub3R1ICovXHJcbiAgICB0aGlzLmN1cnJlbnRJbnRlcnZhbCA9IHRoaXMuaW5pdGlhbEludGVydmFsO1xyXG4gIH07XHJcbiAgdGhpcy5yZWN1cnNpdmUgPSBmdW5jdGlvbigpey8qIG5lcMWZZWtyw712YXQsIGZ1bmtjZSwga3RlcsOhIGTEm2zDoSBzbXnEjWt1ICovXHJcbiAgICBpZih0aGlzLnJ1bm5pbmcpe1xyXG4gICAgICB2YXIgdGltZXIgPSB0aGlzO1xyXG4gICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgdGltZXIudGljaygpO1xyXG4gICAgICAgIHRpbWVyLmN1cnJlbnRJbnRlcnZhbCA9IE1hdGgubWluKHRpbWVyLmN1cnJlbnRJbnRlcnZhbCArIHRpbWVyLmludGVydmFsSW5jcmFzZSwgdGltZXIubWF4aW11bUludGVydmFsKTtcclxuICAgICAgICB0aW1lci5yZWN1cnNpdmUoKTtcclxuICAgICAgfSwgdGltZXIuY3VycmVudEludGVydmFsKTtcclxuICAgIH1cclxuICB9O1xyXG5cclxufVxyXG5cclxubW9kdWxlLmV4cG9ydHMgPSB7XHJcbiAgbmV3SW5zdGFuY2U6IGZ1bmN0aW9uKCl7XHJcbiAgICByZXR1cm4gbmV3IFRpbWVyKCk7XHJcbiAgfVxyXG59XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKlxyXG4gKiBUZW50byBzb3Vib3IgemFzdMWZZcWhdWplIGZsdXggYWtjZSBzb3V2aXNlasOtY8OtIHNlIHrDrXNrw6F2w6Fuw61tIHpwcsOhdi4gVGFrw6kgenByb3N0xZllZGtvdsOhdsOhIGtvbXVuaWthY2kgc2Ugc2VydmVyZW0uXHJcbiAqL1xyXG5cclxuIHZhciBkaXNwYXRjaGVyID0gcmVxdWlyZSgnLi4vLi4vZGlzcGF0Y2hlci9kYXRlbm9kZURpc3BhdGNoZXInKTtcclxuIHZhciBjb25zdGFudHMgPSByZXF1aXJlKCcuLi8uLi9jb25zdGFudHMvQWN0aW9uQ29uc3RhbnRzJyk7XHJcbiB2YXIgRXZlbnRFbWl0dGVyID0gcmVxdWlyZSgnZXZlbnRzJykuRXZlbnRFbWl0dGVyO1xyXG5cclxudmFyIEFjdGlvblR5cGVzID0gY29uc3RhbnRzLkFjdGlvblR5cGVzXHJcbi8qIHphbXlrw6Fuw60gb8WhZXTFmXVqw61jw60gc291YsSbxb5uw6kgcG9zbMOhbsOtIHBvxb5hZGF2a3UgKi9cclxudmFyIGFqYXhMb2NrID0gZmFsc2U7XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHsgIC8qKlxyXG4gICAqIFrDrXNrw6EgemUgc2VydmVydSBwb3NsZWRuw61jaCBuxJtrb2xpayBwcm9ixJtobMO9Y2ggenByw6F2IHMgdcW+aXZhdGVsZW0gcyBkYW7DvW0gaWRcclxuICAgKiBAcGFyYW0ge3N0cmluZ30gdXJsIHVybCwga3RlcsOpIHNlIHB0w6FtIG5hIHpwcsOhdnlcclxuICAgKiBAcGFyYW0ge2ludH0gdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlLCBzZSBrdGVyw71tIHNpIHDDrcWhdVxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBwYXJhbWV0ZXJzUHJlZml4IHByZWZpeCBwxZllZCBwYXJhbWV0cnkgdiB1cmxcclxuICAgKiBAcGFyYW0ge2ludH0gdXN1YWxMb2FkTWVzc2FnZXNDb3VudCAgb2J2eWtsw70gcG/EjWV0IHDFmcOtY2hvesOtY2ggenByw6F2IHYgb2Rwb3bEm2RpXHJcbiAgICovXHJcbiAgY3JlYXRlR2V0SW5pdGlhbE1lc3NhZ2VzOiBmdW5jdGlvbih1cmwsIHVzZXJDb2RlZElkLCBwYXJhbWV0ZXJzUHJlZml4LCB1c3VhbExvYWRNZXNzYWdlc0NvdW50KXtcclxuICAgIHZhciBkYXRhID0ge307XHJcbiAgXHRkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnZnJvbUlkJ10gPSB1c2VyQ29kZWRJZDtcclxuICAgIHRoaXMuYmxvY2tXaW5kb3dVbmxvYWQoJ0plxaF0xJsgc2UgbmHEjcOtdGFqw60genByw6F2eSwgb3ByYXZkdSBjaGNldGUgb2RlasOtdD8nKTtcclxuICAgIHZhciBleHBvcnRPYmplY3QgPSB0aGlzO1xyXG4gICAgJC5nZXRKU09OKHVybCwgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHtcclxuICAgICAgICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gICAgICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5OT19JTklUSUFMX01FU1NBR0VTX0FSUklWRURcclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1lbHNle1xyXG4gICAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk9MREVSX01FU1NBR0VTX0FSUklWRUQsXHJcbiAgICAgICAgICAgIGRhdGE6IHJlc3VsdCxcclxuICAgICAgICAgICAgdXNlckNvZGVkSWQgOiB1c2VyQ29kZWRJZCxcclxuICAgICAgICAgICAgdXN1YWxNZXNzYWdlc0NvdW50IDogdXN1YWxMb2FkTWVzc2FnZXNDb3VudFxyXG4gICAgICAgICAgICAvKiB0YWR5IGJ5Y2ggcMWZw61wYWRuxJsgcMWZaWRhbCBkYWzFocOtIGRhdGEgKi9cclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1cclxuICAgIH0pLmRvbmUoZnVuY3Rpb24oKSB7XHJcbiAgICAgIGV4cG9ydE9iamVjdC5yZWxvYWRXaW5kb3dVbmxvYWQoKTtcclxuICAgIH0pLmZhaWwoZnVuY3Rpb24oKXtcclxuICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgdHlwZTogQWN0aW9uVHlwZXMuTUVTU0FHRV9FUlJPUixcclxuICAgICAgICBlcnJvck1lc3NhZ2U6ICdacHLDoXZ5IHNlIGJvaHXFvmVsIG5lcG9kYcWZaWxvIG5hxI3DrXN0LiBaa3VzdGUgdG8gem5vdnUgcG96ZMSbamkuJ1xyXG4gICAgICB9KTtcclxuICAgIH0pO1xyXG4gIH0sXHJcblxyXG4gIC8qKlxyXG4gICAqIFrDrXNrw6EgemUgc2VydmVydSBuxJtrb2xpayBzdGFyxaHDrWNoIHpwcsOhdlxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgdXJsLCBrdGVyw6kgc2UgcHTDoW0gbmEgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgb2xkZXN0SWQgaWQgbmVqc3RhcsWhw60genByw6F2eSAobmVqbWVuxaHDrSB6bsOhbcOpIGlkKVxyXG4gICAqIEBwYXJhbSAge3N0cmluZ30gcGFyYW1ldGVyc1ByZWZpeCBwcmVmaXggcMWZZWQgcGFyYW1ldHJ5IHYgdXJsXHJcbiAgICogQHBhcmFtIHtpbnR9IHVzdWFsT2xkZXJNZXNzYWdlc0NvdW50ICBvYnZ5a2zDvSBwb8SNZXQgcMWZw61jaG96w61jaCB6cHLDoXYgdiBvZHBvdsSbZGlcclxuICAgKi9cclxuICBjcmVhdGVHZXRPbGRlck1lc3NhZ2VzOiBmdW5jdGlvbih1cmwsIHVzZXJDb2RlZElkLCBvbGRlc3RJZCwgcGFyYW1ldGVyc1ByZWZpeCwgdXN1YWxPbGRlck1lc3NhZ2VzQ291bnQpe1xyXG4gICAgYWpheExvY2sgPSB0cnVlO1xyXG4gICAgdmFyIGRhdGEgPSB7fTtcclxuICBcdGRhdGFbcGFyYW1ldGVyc1ByZWZpeCArICdsYXN0SWQnXSA9IG9sZGVzdElkO1xyXG4gICAgZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ3dpdGhVc2VySWQnXSA9IHVzZXJDb2RlZElkO1xyXG4gICAgJC5nZXRKU09OKHVybCwgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBhamF4TG9jayA9IGZhbHNlO1xyXG4gICAgICAgIGlmKHJlc3VsdC5sZW5ndGggPT0gMCkgcmV0dXJuO1xyXG4gICAgICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gICAgICAgICAgdHlwZTogQWN0aW9uVHlwZXMuT0xERVJfTUVTU0FHRVNfQVJSSVZFRCxcclxuICAgICAgICAgIGRhdGE6IHJlc3VsdCxcclxuICAgICAgICAgIHVzZXJDb2RlZElkIDogdXNlckNvZGVkSWQsXHJcbiAgICAgICAgICBvbGRlcnNJZCA6IG9sZGVzdElkLFxyXG4gICAgICAgICAgdXN1YWxNZXNzYWdlc0NvdW50IDogdXN1YWxPbGRlck1lc3NhZ2VzQ291bnRcclxuICAgICAgICB9KTtcclxuICAgIH0pLmZhaWwoZnVuY3Rpb24oKXtcclxuICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgdHlwZTogQWN0aW9uVHlwZXMuTUVTU0FHRV9FUlJPUixcclxuICAgICAgICBlcnJvck1lc3NhZ2U6ICdacHLDoXZ5IHNlIGJvaHXFvmVsIG5lcG9kYcWZaWxvIG5hxI3DrXN0LiBaa3VzdGUgdG8gem5vdnUgcG96ZMSbamkuJ1xyXG4gICAgICB9KTtcclxuICAgIH0pO1xyXG4gIH0sXHJcblxyXG4gIC8qKlxyXG4gICAqIFBvxaFsZSBuYSBzZXJ2ZXIgenByw6F2dS5cclxuICAgKiBAcGFyYW0ge3N0cmluZ30gdXJsIHVybCwga3RlcsOpIHNlIHB0w6FtIG5hIHpwcsOhdnlcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlXHJcbiAgICogQHBhcmFtICB7U3RyaW5nfSBtZXNzYWdlIHRleHQgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge2ludH0gbGFzdElkIHBvc2xlZG7DrSB6bsOhbcOpIGlkXHJcbiAgICovXHJcbiAgY3JlYXRlU2VuZE1lc3NhZ2U6IGZ1bmN0aW9uKHVybCwgdXNlckNvZGVkSWQsIG1lc3NhZ2UsIGxhc3RJZCl7XHJcbiAgICBhamF4TG9jayA9IHRydWU7XHJcbiAgICB2YXIgZGF0YSA9IHtcclxuICAgICAgdG86IHVzZXJDb2RlZElkLFxyXG4gICAgICB0eXBlOiAndGV4dE1lc3NhZ2UnLFxyXG4gICAgICB0ZXh0OiBtZXNzYWdlLFxyXG4gICAgICBsYXN0aWQ6IGxhc3RJZFxyXG4gICAgfTtcclxuICAgIHRoaXMuYmxvY2tXaW5kb3dVbmxvYWQoJ1pwcsOhdmEgc2Ugc3TDoWxlIG9kZXPDrWzDoSwgcHJvc8OtbWUgcG/EjWtlanRlIG7Em2tvbGlrIHNla3VuZCBhIHBhayB0byB6a3VzdGUgem5vdmEuJyk7XHJcbiAgICB2YXIgZXhwb3J0T2JqZWN0ID0gdGhpcztcclxuICAgIHZhciBqc29uID0gSlNPTi5zdHJpbmdpZnkoZGF0YSk7XHJcbiAgXHRcdCQuYWpheCh7XHJcbiAgXHRcdFx0ZGF0YVR5cGU6IFwianNvblwiLFxyXG4gIFx0XHRcdHR5cGU6ICdQT1NUJyxcclxuICBcdFx0XHR1cmw6IHVybCxcclxuICBcdFx0XHRkYXRhOiBqc29uLFxyXG4gIFx0XHRcdGNvbnRlbnRUeXBlOiAnYXBwbGljYXRpb24vanNvbjsgY2hhcnNldD11dGYtOCcsXHJcbiAgXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xyXG4gICAgICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5ORVdfTUVTU0FHRVNfQVJSSVZFRCxcclxuICAgICAgICAgICAgZGF0YTogcmVzdWx0LFxyXG4gICAgICAgICAgICB1c2VyQ29kZWRJZCA6IHVzZXJDb2RlZElkXHJcbiAgICAgICAgICB9KTtcclxuICAgICAgICB9LFxyXG4gICAgICAgIGNvbXBsZXRlOiBmdW5jdGlvbigpe1xyXG4gICAgICAgICAgYWpheExvY2sgPSBmYWxzZTtcclxuICAgICAgICAgIGV4cG9ydE9iamVjdC5yZWxvYWRXaW5kb3dVbmxvYWQoKTtcclxuICAgICAgICB9LFxyXG4gICAgICAgIGVycm9yOiBmdW5jdGlvbigpe1xyXG4gICAgICAgICAgZGlzcGF0Y2hlci5kaXNwYXRjaCh7XHJcbiAgICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk1FU1NBR0VfRVJST1IsXHJcbiAgICAgICAgICAgIGVycm9yTWVzc2FnZTogJ1ZhxaFpIHpwcsOhdnUgc2UgYm9odcW+ZWwgbmVwb2RhxZlpbG8gb2Rlc2xhdC4gWmt1c3RlIHRvIHpub3Z1IHBvemTEm2ppLidcclxuICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1cclxuICBcdFx0fSk7XHJcbiAgfSxcclxuXHJcbiAgLyoqXHJcbiAgICogWmVwdMOhIHNlIHNlcnZlcnUgbmEgbm92w6kgenByw6F2eVxyXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgdXJsLCBrdGVyw6kgc2UgcHTDoW0gbmEgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtpbnR9IGxhc3RJZCBwb3NsZWRuw60gem7DoW3DqSBpZFxyXG4gICAqIEBwYXJhbSAge3N0cmluZ30gcGFyYW1ldGVyc1ByZWZpeCBwcmVmaXggcMWZZWQgcGFyYW1ldHJ5IHYgdXJsXHJcbiAgICovXHJcbiAgY3JlYXRlUmVmcmVzaE1lc3NhZ2VzOiBmdW5jdGlvbih1cmwsIHVzZXJDb2RlZElkLCBsYXN0SWQsIHBhcmFtZXRlcnNQcmVmaXgpe1xyXG4gICAgaWYoYWpheExvY2spIHJldHVybjtcclxuICAgIHZhciBkYXRhID0ge307XHJcbiAgXHRkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnbGFzdGlkJ10gPSBsYXN0SWQ7XHJcbiAgICBkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAncmVhZGVkTWVzc2FnZXMnXSA9IFtsYXN0SWRdO1xyXG4gICAgJC5nZXRKU09OKHVybCwgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHJldHVybjtcclxuICAgICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICAgIHR5cGU6IEFjdGlvblR5cGVzLk5FV19NRVNTQUdFU19BUlJJVkVELFxyXG4gICAgICAgICAgZGF0YTogcmVzdWx0LFxyXG4gICAgICAgICAgdXNlckNvZGVkSWQgOiB1c2VyQ29kZWRJZFxyXG4gICAgICAgIH0pO1xyXG4gICAgfSkuZmFpbChmdW5jdGlvbigpe1xyXG4gICAgICBkaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICAgICAgICB0eXBlOiBBY3Rpb25UeXBlcy5NRVNTQUdFX0VSUk9SLFxyXG4gICAgICAgIGVycm9yTWVzc2FnZTogJ1pwcsOhdnkgc2UgYm9odcW+ZWwgbmVwb2RhxZlpbG8gbmHEjcOtc3QuIFprdXN0ZSB0byB6bm92dSBwb3pkxJtqaS4nXHJcbiAgICAgIH0pO1xyXG4gICAgfSk7XHJcbiAgfSxcclxuXHJcbiAgLyoqXHJcbiAgXHQgKiBQxZlpIHBva3VzdSB6YXbFmcOtdCBuZWJvIG9ibm92aXQgb2tubyBzZSB6ZXB0w6EgdcW+aXZhdGVsZSxcclxuICBcdCAqIHpkYSBjaGNlIG9rbm8gc2t1dGXEjW7EmyB6YXbFmcOtdC9vYm5vdml0LiBUb3RvIGTEm2zDoSB2IGthxb5kw6ltIHDFmcOtcGFkxJssIGRva3VkXHJcbiAgXHQgKiBzZSBuZXphdm9sw6EgcmVsb2FkV2luZG93VW5sb2FkXHJcbiAgXHQgKiBAcGFyYW0ge1N0cmluZ30gcmVhc29uIGTFr3ZvZCB1dmVkZW7DvSB2IGRpYWxvZ3VcclxuICBcdCAqL1xyXG4gIFx0YmxvY2tXaW5kb3dVbmxvYWQ6IGZ1bmN0aW9uKHJlYXNvbikge1xyXG4gIFx0XHR3aW5kb3cub25iZWZvcmV1bmxvYWQgPSBmdW5jdGlvbiAoKSB7XHJcbiAgXHRcdFx0cmV0dXJuIHJlYXNvbjtcclxuICBcdFx0fTtcclxuICBcdH0sXHJcblxyXG4gIFx0LyoqXHJcbiAgXHQgKiBWeXBuZSBobMOtZMOhbsOtIHphdsWZZW7DrS9vYm5vdmVuw60gb2tuYSBhIHZyw6F0w60gamVqIGRvIHBvxI3DoXRlxI1uw61obyBzdGF2dS5cclxuICBcdCAqL1xyXG4gIFx0cmVsb2FkV2luZG93VW5sb2FkOiBmdW5jdGlvbigpIHtcclxuICBcdFx0d2luZG93Lm9uYmVmb3JldW5sb2FkID0gZnVuY3Rpb24gKCkge1xyXG4gIFx0XHRcdHZhciB1bnNlbmQgPSBmYWxzZTtcclxuICBcdFx0XHQkLmVhY2goJChcIi5tZXNzYWdlSW5wdXRcIiksIGZ1bmN0aW9uICgpIHsvL3Byb2pkZSB2c2VjaG55IHRleHRhcmVhIGNoYXR1XHJcbiAgXHRcdFx0XHRpZiAoJC50cmltKCQodGhpcykudmFsKCkpKSB7Ly91IGthemRlaG8gemtvdW1hIGhvZG5vdHUgYmV6IHdoaXRlc3BhY3VcclxuICBcdFx0XHRcdFx0dW5zZW5kID0gdHJ1ZTtcclxuICBcdFx0XHRcdH1cclxuICBcdFx0XHR9KTtcclxuICBcdFx0XHRpZiAodW5zZW5kKSB7XHJcbiAgXHRcdFx0XHRyZXR1cm4gJ03DoXRlIHJvemVwc2Fuw70gcMWZw61zcMSbdmVrLiBDaGNldGUgdHV0byBzdHLDoW5rdSBwxZllc3RvIG9wdXN0aXQ/JztcclxuICBcdFx0XHRcdC8qIGhsw6HFoWthLCBjbyBzZSBvYmpldsOtIHDFmWkgcG9rdXN1IG9ibm92aXQvemF2xZnDrXQgb2tubywgemF0w61tY28gbcOhIHXFvml2YXRlbCByb3plcHNhbm91IHpwcsOhdnUgKi9cclxuICBcdFx0XHR9XHJcbiAgXHRcdH07XHJcbiAgXHR9XHJcbn07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcblxyXG52YXIga2V5TWlycm9yID0gcmVxdWlyZSgna2V5bWlycm9yJyk7XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuXHJcbiAgLyogdHlweSBha2PDrSwga3RlcsOpIG1vaG91IG5hc3RhdCAqL1xyXG4gIEFjdGlvblR5cGVzOiBrZXlNaXJyb3Ioe1xyXG4gICAgLyogQ0hBVCAqL1xyXG4gICAgTk9fSU5JVElBTF9NRVNTQUdFU19BUlJJVkVEIDogbnVsbCwvKiBwxZlpxaFsYSBvZHBvdsSbxI8gcMWZaSBwcnZvdG7DrW0gbmHEjcOtdMOhbsOtIHpwcsOhdiwgYWxlIGJ5bGEgcHLDoXpkbsOhKi9cclxuICAgIE9MREVSX01FU1NBR0VTX0FSUklWRUQgOiBudWxsLC8qIHDFmWnFoWx5IHN0YXLFocOtIChkb25hxI10ZW7DqSB0bGHEjcOtdGtlbSkgenByw6F2eSAqL1xyXG4gICAgTkVXX01FU1NBR0VTX0FSUklWRUQgOiBudWxsLC8qIHDFmWnFoWx5IG5vdsOpIHpwcsOhdnkqL1xyXG4gICAgTUVTU0FHRV9FUlJPUiA6IG51bGwgLyogbsSbY28gc2UgbmVwb3ZlZGxvICovXHJcbiAgfSlcclxuXHJcbn07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuXHJcbiAgLyogc3BlY2nDoWxuw60gxZlldMSbemNlIHJvemxpxaFvdmFuw6kgY2hhdGVtICovXHJcbiAgTWVzc2FnZUNvbnN0YW50czoge1xyXG4gICAgU0VORF9TTEFQIDogJ0Ahc2xhcDQ0NCcsXHJcbiAgfVxyXG5cclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxudmFyIERpc3BhdGNoZXIgPSByZXF1aXJlKCdmbHV4JykuRGlzcGF0Y2hlcjtcclxuXHJcbm1vZHVsZS5leHBvcnRzID0gbmV3IERpc3BhdGNoZXIoKTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxudmFyIERpc3BhdGNoZXIgPSByZXF1aXJlKCcuLi8uLi9kaXNwYXRjaGVyL2RhdGVub2RlRGlzcGF0Y2hlcicpO1xyXG5pZih0eXBlb2YgamVzdCAhPT0gJ3VuZGVmaW5lZCcpe1xyXG4gICBqZXN0LmF1dG9Nb2NrT2ZmKCk7Lyogb2JlemxpxI1rYSBrdsWvbGkgdGVzdG92w6Fuw60gKi9cclxuICAgdmFyIGNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMnKTtcclxuICAgamVzdC5hdXRvTW9ja09uKCk7XHJcbn1lbHNle1xyXG4gIHZhciBjb25zdGFudHMgPSByZXF1aXJlKCcuLi8uLi9jb25zdGFudHMvQWN0aW9uQ29uc3RhbnRzJyk7XHJcbn1cclxudmFyIE1lc3NhZ2VDb25zdGFudHMgPSByZXF1aXJlKCcuLi8uLi9jb25zdGFudHMvQ2hhdENvbnN0YW50cycpLk1lc3NhZ2VDb25zdGFudHM7XHJcblxyXG5cclxudmFyIEV2ZW50RW1pdHRlciA9IHJlcXVpcmUoJ2V2ZW50cycpLkV2ZW50RW1pdHRlcjtcclxudmFyIGFzc2lnbiA9IHJlcXVpcmUoJ29iamVjdC1hc3NpZ24nKTtcclxuXHJcbnZhciBDSEFOR0VfRVZFTlQgPSAnY2hhbmdlJztcclxuXHJcbnZhciBfZGF0YVZlcnNpb24gPSAwOy8qIGtvbGlrcsOhdCBzZSB1xb4gem3Em25pbGEgZGF0YSAqL1xyXG52YXIgX21lc3NhZ2VzID0gW107XHJcbnZhciBfaW5mb01lc3NhZ2VzID0gW107XHJcbnZhciBfdGhlcmVJc01vcmUgPSB0cnVlO1xyXG5cclxudmFyIE1lc3NhZ2VTdG9yZSA9IGFzc2lnbih7fSwgRXZlbnRFbWl0dGVyLnByb3RvdHlwZSwge1xyXG4gIC8qIHRyaWdnZXIgem3Em255ICovXHJcbiAgZW1pdENoYW5nZTogZnVuY3Rpb24oKSB7XHJcbiAgICBfZGF0YVZlcnNpb24rKztcclxuICAgIGlmKF9tZXNzYWdlcy5sZW5ndGggPT0gMCkgX3RoZXJlSXNNb3JlID0gZmFsc2U7XHJcbiAgICB0aGlzLmVtaXQoQ0hBTkdFX0VWRU5UKTtcclxuICB9LFxyXG4gIC8qIHRvdXRvIG1ldG9kb3UgbHplIHBvdsSbc2l0IGxpc3RlbmVyIHJlYWd1asOtY8OtIHDFmWkgem3Em27EmyovXHJcbiAgYWRkQ2hhbmdlTGlzdGVuZXI6IGZ1bmN0aW9uKGNhbGxiYWNrKSB7XHJcbiAgICB0aGlzLm9uKENIQU5HRV9FVkVOVCwgY2FsbGJhY2spO1xyXG4gIH0sXHJcbiAgLyogdG91dG8gbWV0b2RvdSBsemUgbGlzdGVuZXIgb2Rlam1vdXQqL1xyXG4gIHJlbW92ZUNoYW5nZUxpc3RlbmVyOiBmdW5jdGlvbihjYWxsYmFjaykge1xyXG4gICAgdGhpcy5yZW1vdmVMaXN0ZW5lcihDSEFOR0VfRVZFTlQsIGNhbGxiYWNrKTtcclxuICB9LFxyXG4gIC8qIHZyYWPDrSBzdGF2IHpwcsOhdiB2IGplZGluw6ltIG9iamVrdHUqL1xyXG4gIGdldFN0YXRlOiBmdW5jdGlvbigpIHtcclxuICAgIHJldHVybiB7XHJcbiAgICAgIG1lc3NhZ2VzOiBfbWVzc2FnZXMsXHJcbiAgICAgIGluZm9NZXNzYWdlczogX2luZm9NZXNzYWdlcyxcclxuICAgICAgdGhlcmVJc01vcmU6IF90aGVyZUlzTW9yZSxcclxuICAgICAgZGF0YVZlcnNpb246IF9kYXRhVmVyc2lvblxyXG4gICAgfTtcclxuICB9XHJcblxyXG59KTtcclxuXHJcbk1lc3NhZ2VTdG9yZS5kaXNwYXRjaFRva2VuID0gRGlzcGF0Y2hlci5yZWdpc3RlcihmdW5jdGlvbihhY3Rpb24pIHtcclxuICB2YXIgdHlwZXMgPSBjb25zdGFudHMuQWN0aW9uVHlwZXM7XHJcbiAgc3dpdGNoKGFjdGlvbi50eXBlKXtcclxuICAgIGNhc2UgdHlwZXMuTkVXX01FU1NBR0VTX0FSUklWRUQgOlxyXG4gICAgICBhcHBlbmREYXRhSW50b01lc3NhZ2VzKGFjdGlvbi51c2VyQ29kZWRJZCwgYWN0aW9uLmRhdGEsIGFjdGlvbi51c3VhbE1lc3NhZ2VzQ291bnQpO1xyXG4gICAgICBNZXNzYWdlU3RvcmUuZW1pdENoYW5nZSgpO1xyXG4gICAgICBicmVhaztcclxuICAgIGNhc2UgdHlwZXMuT0xERVJfTUVTU0FHRVNfQVJSSVZFRCA6XHJcbiAgICAgIHByZXBlbmREYXRhSW50b01lc3NhZ2VzKGFjdGlvbi51c2VyQ29kZWRJZCwgYWN0aW9uLmRhdGEsIGFjdGlvbi51c3VhbE1lc3NhZ2VzQ291bnQpO1xyXG4gICAgICBNZXNzYWdlU3RvcmUuZW1pdENoYW5nZSgpO1xyXG4gICAgICBicmVhaztcclxuICAgIGNhc2UgdHlwZXMuTk9fSU5JVElBTF9NRVNTQUdFU19BUlJJVkVEOlxyXG4gICAgICBNZXNzYWdlU3RvcmUuZW1pdENoYW5nZSgpOy8qIGtkecW+IG5lcMWZaWpkb3Ugxb7DoWRuw6kgenByw6F2eSBwxZlpIGluaWNpYWxpemFjaSwgZMOhIHRvIG5hamV2byAqL1xyXG4gICAgICBicmVhaztcclxuICAgIGNhc2UgdHlwZXMuTUVTU0FHRV9FUlJPUjpcclxuICAgICAgYWxlcnQoJ0NoeWJhIHPDrXTEmzogJyArIGFjdGlvbi5lcnJvck1lc3NhZ2UpO1xyXG4gICAgICBicmVhaztcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqXHJcbiAqIE5hc3RhdsOtIHpwcsOhdnkgemUgc3RhbmRhcmRuw61obyBKU09OdSBjaGF0dSAodml6IGRva3VtZW50YWNlKSBkbyBzdGF2dSB0b2hvdG8gU3RvcmUgemEgZXhpc3R1asOtY8OtIHpwcsOhdnkuXHJcbiAqIEBwYXJhbSAge2ludH0gdXNlckNvZGVkSWQgaWQgdcW+aXZhdGVsZSwgb2Qga3RlcsOpaG8gY2hjaSBuYcSNw61zdCB6cHLDoXZ5XHJcbiAqIEBwYXJhbSAge2pzb259IGpzb25EYXRhICBkYXRhIHplIHNlcnZlcnVcclxuICovXHJcbnZhciBhcHBlbmREYXRhSW50b01lc3NhZ2VzID0gZnVuY3Rpb24odXNlckNvZGVkSWQsIGpzb25EYXRhKXtcclxuICB2YXIgcmVzdWx0ID0ganNvbkRhdGFbdXNlckNvZGVkSWRdO1xyXG4gIHZhciByZXN1bHRNZXNzYWdlcyA9IGZpbHRlckluZm9NZXNzYWdlcyhyZXN1bHQubWVzc2FnZXMpO1xyXG4gIHJlc3VsdE1lc3NhZ2VzID0gbW9kaWZ5TWVzc2FnZXMocmVzdWx0TWVzc2FnZXMpO1xyXG4gIF9tZXNzYWdlcyA9IF9tZXNzYWdlcy5jb25jYXQocmVzdWx0TWVzc2FnZXMpO1xyXG59O1xyXG5cclxuLyoqXHJcbiAqIE5hc3RhdsOtIHpwcsOhdnkgemUgc3RhbmRhcmRuw61obyBKU09OdSBjaGF0dSAodml6IGRva3VtZW50YWNlKSBkbyBzdGF2dSB0b2hvdG8gU3RvcmUgcMWZZWQgZXhpc3R1asOtY8OtIHpwcsOhdnkuXHJcbiAqIEBwYXJhbSAge2ludH0gdXNlckNvZGVkSWQgaWQgdcW+aXZhdGVsZSwgb2Qga3RlcsOpaG8gY2hjaSBuYcSNw61zdCB6cHLDoXZ5XHJcbiAqIEBwYXJhbSAge2pzb259IGpzb25EYXRhICBkYXRhIHplIHNlcnZlcnVcclxuICogQHBhcmFtICB7aW50fSB1c3VhbE1lc3NhZ2VzQ291bnQgb2J2eWtsw70gcG/EjWV0IHpwcsOhdiAtIHBva3VkIGplIGRvZHLFvmVuLCB6YWhvZMOtIG5lanN0YXLFocOtIHpwcsOhdnUgKHBva3VkIGplIHpwcsOhdiBkb3N0YXRlaylcclxuICogYSBrb21wb25lbnTEmyBwb2RsZSB0b2hvIG5hc3RhdsOtIHN0YXYsIMW+ZSBuYSBzZXJ2ZXJ1IGplxaF0xJsganNvdS91xb4gbmVqc291IGRhbMWhw60genByw6F2eVxyXG4gKi9cclxudmFyIHByZXBlbmREYXRhSW50b01lc3NhZ2VzID0gZnVuY3Rpb24odXNlckNvZGVkSWQsIGpzb25EYXRhLCB1c3VhbE1lc3NhZ2VzQ291bnQpe1xyXG4gIHZhciB0aGVyZUlzTW9yZSA9IHRydWU7XHJcbiAgdmFyIHJlc3VsdCA9IGpzb25EYXRhW3VzZXJDb2RlZElkXTtcclxuICBpZihyZXN1bHQubWVzc2FnZXMubGVuZ3RoIDwgdXN1YWxNZXNzYWdlc0NvdW50KXsvKiBwb2t1ZCBtw6FtIG3DqW7EmyB6cHLDoXYgbmXFviBqZSBvYnZ5a2zDqSovXHJcbiAgICB0aGVyZUlzTW9yZSA9IGZhbHNlO1xyXG4gIH1lbHNle1xyXG4gICAgcmVzdWx0Lm1lc3NhZ2VzLnNoaWZ0KCk7Lyogb2RlYmVydSBwcnZuw60genByw6F2dSAqL1xyXG4gIH1cclxuICBfdGhlcmVJc01vcmUgPSB0aGVyZUlzTW9yZTtcclxuICB2YXIgdGV4dE1lc3NhZ2VzID0gZmlsdGVySW5mb01lc3NhZ2VzKHJlc3VsdC5tZXNzYWdlcylcclxuICByZXN1bHQubWVzc2FnZXMgPSBtb2RpZnlNZXNzYWdlcyh0ZXh0TWVzc2FnZXMpO1xyXG4gIF9tZXNzYWdlcyA9IHJlc3VsdC5tZXNzYWdlcy5jb25jYXQoX21lc3NhZ2VzKTtcclxufTtcclxuXHJcbi8qKlxyXG4gKiBPZGZpbHRydWplIHogZGF0IGluZm96cHLDoXZ5IGEgdnl0xZnDrWTDrSBqZSB6dmzDocWhxaUgZG8gZ2xvYsOhbG7DrSBwcm9txJtubsOpXHJcbiAqIEBwYXJhbSB7anNvbn0gbWVzc2FnZXMgenByw6F2eSBwxZlpamF0w6kgemUgc2VydmVydVxyXG4gKi9cclxudmFyIGZpbHRlckluZm9NZXNzYWdlcyA9IGZ1bmN0aW9uKG1lc3NhZ2VzKXtcclxuICBfaW5mb01lc3NhZ2VzID0gW107XHJcbiAgZm9yKHZhciBpID0gMDsgaSA8IG1lc3NhZ2VzLmxlbmd0aDsgaSsrKXtcclxuICAgIGlmKG1lc3NhZ2VzW2ldLnR5cGUgPT0gMSl7Lyoga2R5xb4gamUgdG8gaW5mb3pwcsOhdmEgKi9cclxuICAgICAgYWRkVG9JbmZvTWVzc2FnZXMobWVzc2FnZXNbaV0pO1xyXG4gICAgICBtZXNzYWdlcy5zcGxpY2UoaSwxKTsvKiBvZHN0cmFuxJtuw60genByw6F2eSAqL1xyXG4gICAgfVxyXG4gIH1cclxuICByZXR1cm4gbWVzc2FnZXM7XHJcbn07XHJcblxyXG4vKipcclxuICogUMWZaWTDoSB6cHLDoXZ1IGsgaW5mb3pwcsOhdsOhbSwgcG9rdWQgbWV6aSBuaW1pIGplxaF0xJsgbmVuw61cclxuICogQHBhcmFtICB7anNvbn0gbWVzc2FnZSB6cHLDoXZhIHDFmWlqYXTDoSB6ZSBzZXJ2ZXJ1XHJcbiAqL1xyXG52YXIgYWRkVG9JbmZvTWVzc2FnZXMgPSBmdW5jdGlvbihtZXNzYWdlKSB7XHJcbiAgdmFyIGFscmVhZHlFeGlzdHMgPSBmYWxzZTtcclxuICBfaW5mb01lc3NhZ2VzLmZvckVhY2goZnVuY3Rpb24oaW5mb01lc3NhZ2Upe1xyXG4gICAgaWYoaW5mb01lc3NhZ2UudGV4dCA9PSBtZXNzYWdlLnRleHQpe1xyXG4gICAgICBhbHJlYWR5RXhpc3RzID0gdHJ1ZTtcclxuICAgICAgcmV0dXJuO1xyXG4gICAgfVxyXG4gIH0pO1xyXG4gIGlmKCFhbHJlYWR5RXhpc3RzKXtcclxuICAgIF9pbmZvTWVzc2FnZXMucHVzaChtZXNzYWdlKTtcclxuICB9XHJcbiAgfTtcclxuICAvKipcclxuICAgKiBNb2RpZmlrdWplIHRleHQgZGFuw71jaCB6cHLDoXYgKHNlbSBwYXTFmcOtIHplam3DqW5hIG5haHJhem92w6Fuw60gdXLEjWl0w71jaCDEjcOhc3TDrSBvYnLDoXprZW0gLSBzbWFqbMOta3ksIGZhY2t5LCBwb3NsYW7DqSB1cmwgb2Jyw6F6a3UuLi4pXHJcbiAgICogQHBhcmFtICB7T2JqZWN0fSBtZXNzYWdlcyBzYWRhIHpwcsOhdlxyXG4gICAqL1xyXG4gIHZhciBtb2RpZnlNZXNzYWdlcyA9IGZ1bmN0aW9uKG1lc3NhZ2VzKSB7XHJcbiAgICBtZXNzYWdlcy5mb3JFYWNoKGZ1bmN0aW9uKG1lc3NhZ2Upe1xyXG4gICAgICBtZXNzYWdlLmltYWdlcyA9IFtdO1xyXG4gICAgICAvKiBuYWhyYXplbsOtIHNwZWNpw6FsbsOtaG8gc3ltYm9sdSBvYnLDoXprZW0gKi9cclxuICAgICAgY2hlY2tTbGFwKG1lc3NhZ2UpO1xyXG4gICAgfSk7XHJcbiAgICByZXR1cm4gbWVzc2FnZXM7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogWmtvbnRyb2x1amUsIHpkYSB6cHLDoXZhIG5lb2JzYWh1amUgc3ltYm9sIGZhY2t5XHJcbiAgICogQHBhcmFtICB7T2JqZWN0fSBtZXNzYWdlIG9iamVrdCBqZWRuw6kgenByw6F2eVxyXG4gICAqL1xyXG4gIHZhciBjaGVja1NsYXAgPSBmdW5jdGlvbihtZXNzYWdlKXtcclxuICAgIGlmIChtZXNzYWdlLnRleHQuaW5kZXhPZihNZXNzYWdlQ29uc3RhbnRzLlNFTkRfU0xBUCkgPj0gMCl7Lyogb2JzYWh1amUgc3ltYm9sIGZhY2t5ICovXHJcbiAgICAgIG1lc3NhZ2UuaW1hZ2VzLnB1c2goey8qIHDFmWlkw6Fuw60gZmFja3kgZG8gcG9sZSBvYnLDoXprxa8gKi9cclxuICAgICAgICB1cmw6ICcuLi9pbWFnZXMvY2hhdENvbnRlbnQvc2xhcC1pbWFnZS5wbmcnLFxyXG4gICAgICAgIHdpZHRoOiAnMjU2J1xyXG4gICAgICB9KTtcclxuICAgICAgbWVzc2FnZS50ZXh0ID0gbWVzc2FnZS50ZXh0LnJlcGxhY2UobmV3IFJlZ0V4cChNZXNzYWdlQ29uc3RhbnRzLlNFTkRfU0xBUCwgJ2cnKSwgJycpOy8qIHNtYXrDoW7DrSB2xaFlY2ggc3RyaW5nxa8gcHJvIGZhY2t1ICovXHJcbiAgICB9XHJcbiAgfVxyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBNZXNzYWdlU3RvcmU7XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5cclxuLyoqKioqKioqKioqICBJTklDSUFMSVpBQ0UgICoqKioqKioqKioqL1xyXG52YXIgY2hhdFJvb3QgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0V2luZG93Jyk7XHJcbmlmKHR5cGVvZihjaGF0Um9vdCkgIT0gJ3VuZGVmaW5lZCcgJiYgY2hhdFJvb3QgIT0gbnVsbCl7LypleGlzdHVqZSBlbGVtZW50IHBybyBjaGF0Ki9cclxuICB2YXIgQ2hhdCA9IHJlcXVpcmUoJy4vY2hhdC9yZWFjdENoYXQnKTtcclxuICB2YXIgbG9nZ2VkVXNlciA9IHtcclxuICAgIG5hbWU6IGNoYXRSb290LmRhdGFzZXQudXNlcm5hbWUsXHJcbiAgICBhbGxvd2VkVG9TbGFwOiAoY2hhdFJvb3QuZGF0YXNldC5jYW5zbGFwID09ICd0cnVlJyksXHJcbiAgICBocmVmOiBjaGF0Um9vdC5kYXRhc2V0LnVzZXJocmVmLFxyXG4gICAgcHJvZmlsZVBob3RvVXJsOiBjaGF0Um9vdC5kYXRhc2V0LnByb2ZpbGVwaG90b3VybFxyXG4gIH07XHJcbiAgUmVhY3QucmVuZGVyKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KENoYXQuQ2hhdFdpbmRvdywge3VzZXJDb2RlZElkOiBjaGF0Um9vdC5kYXRhc2V0LnVzZXJpbmNoYXRjb2RlZGlkLCBsb2dnZWRVc2VyOiBsb2dnZWRVc2VyfSksXHJcbiAgICAgIGNoYXRSb290XHJcbiAgKTtcclxufVxyXG4iXX0=
