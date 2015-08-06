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
var actions = require('../flux/actions/chat/ExampleActionCreators');
var messageStore = require('../flux/stores/chat/MessageStore');

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
var usualGetOlderMessagesCount = reactGetOlderMessages.dataset.maxmessages;
var usualLoadMessagesCount = reactLoadMessages.dataset.maxmessages;

/***********  DEFINICE  ***********/
/** Část okna, která má svislý posuvník - obsahuje zprávy, tlačítko pro donačítání... */
var MessagesWindow = React.createClass({displayName: "MessagesWindow",
  getInitialState: function() {
    return {messages: [], thereIsMore: true, href: '' };
  },
  componentDidMount: function() {
    getInitialMessages(this, this.props.userCodedId, prependDataIntoComponent);
    actions.createTest();
  },
  render: function() {
    var messages = this.state.messages;
    var oldestId = this.getOldestId(messages);
    /* sestavení odkazu pro tlačítko */
    var moreButtonLink = reactGetOlderMessagesLink + '&' + parametersPrefix + 'lastId=' + oldestId + '&' + parametersPrefix + 'withUserId=' + this.props.userCodedId;
    return (
      React.createElement("div", {className: "messagesWindow"}, 
        React.createElement(LoadMoreButton, {loadHref: moreButtonLink, loadTo: this, oldestId: oldestId, thereIsMore: this.state.thereIsMore, userCodedId: this.props.userCodedId}), 
        messages.map(function(message){
            return React.createElement(Message, {key: message.id, messageData: message, userHref: message.profileHref, profilePhotoUrl: message.profilePhotoUrl});
        })
        
      )
    );
  },
  getOldestId: function(messages){
    return (messages[0]) ? messages[0].id : 9007199254740991; /*nastavení hodnoty nebo maximální hodnoty, když není*/
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
    getOlderMessages(this.props.loadTo, this.props.userCodedId, this.props.oldestId, prependDataIntoComponent);
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
    sendMessage(this, this.props.userCodedId, message, appendDataIntoComponent);
  }
});

module.exports = {
  /** Okno celého chatu s jedním uživatelem */
  ChatWindow: React.createClass({displayName: "ChatWindow",
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



  /***********  KOMUNIKACE (jQuery) ***********/

  /**
   * Získá ze serveru posledních několik proběhlých zpráv s uživatelem s daným id
   * @param  {ReactClass} component komponenta, která si vyžádala data
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {Function} callback    funkce, která se zavolá při obdržení odpovědi
   */
  var getInitialMessages = function(component, userCodedId, callback){
    var data = {};
  	data[parametersPrefix + 'fromId'] = userCodedId;

    $.getJSON(reactLoadMessagesLink, data, function(result){
        if(result.length == 0) return;
        callback(component, userCodedId, result, usualLoadMessagesCount);
    });
  };

  /**
   * Získá ze serveru několik starších zpráv
   * @param  {ReactClass} component komponenta, která bude aktualizována daty
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {int}   oldestId id nejstarší zprávy (nejmenší známé id)
   * @param  {Function} callback    funkce, která se zavolá při obdržení odpovědi
   */
  var getOlderMessages = function(component, userCodedId, oldestId, callback){
    var data = {};
  	data[parametersPrefix + 'lastId'] = oldestId;
    data[parametersPrefix + 'withUserId'] = userCodedId;
    $.getJSON(reactGetOlderMessagesLink, data, function(result){
        if(result.length == 0) return;
        callback(component, userCodedId, result, usualGetOlderMessagesCount);
    });
  };

  /**
   * Pošle na server zprávu.
   * @param  {ReactClass} component komponenta, která bude aktualizována daty
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {String} message text zprávy
   * @param  {Function} callback    funkce, která se zavolá při obdržení odpovědi (odeslaná zpráva přijde zpět)
   */
  var sendMessage = function(component, userCodedId, message, callback){
    var data = {
      to: userCodedId,
      type: 'textMessage',
      text: message
    };
    var json = JSON.stringify(data);
  		$.ajax({
  			dataType: "json",
  			type: 'POST',
  			url: reactSendMessageLink,
  			data: json,
  			contentType: 'application/json; charset=utf-8',
  			success: function(result){
          callback(component, userCodedId, result);
        }
  		});
  };

  /***********  CALLBACK FUNKCE  ***********/

  /**
   * Nastaví zprávy ze standardního JSONu chatu (viz dokumentace) do state předané komponenty na začátek před ostatní zprávy.
   * @param  {ReactClass} component komponenta
   * @param  {int} userCodedId id uživatele, od kterého chci načíst zprávy
   * @param  {json} jsonData  data ze serveru
   * @param  {int} usualMessagesCount obvyklý počet zpráv - pokud je dodržen, zahodí nejstarší zprávu (pokud je zpráv dostatek)
   * a komponentě podle toho nastaví stav, že na serveru ještě jsou/už nejsou další zprávy
   */
  var prependDataIntoComponent = function(component, userCodedId, jsonData, usualMessagesCount){
    var thereIsMore = true;
    var result = jsonData[userCodedId];
    if(result.messages.length < usualMessagesCount){/* pokud mám méně zpráv než je obvyklé*/
      thereIsMore = false;
    }else{
      result.messages.shift();/* odeberu první zprávu */
    }
    result.thereIsMore = thereIsMore;
    result.messages = result.messages.concat(component.state.messages);
    component.setState(result);
  };

  /**
   * Nastaví zprávy ze standardního JSONu chatu (viz dokumentace) do state předané komponenty za ostatní zprávy.
   * @param  {ReactClass} component komponenta
   * @param  {int} userCodedId id uživatele, od kterého chci načíst zprávy
   * @param  {json} jsonData  data ze serveru
   */
  var appendDataIntoComponent = function(component, userCodedId, jsonData){
    var result = jsonData[userCodedId];
    result.thereIsMore = thereIsMore;
    result.messages = component.state.messages.concat(result.messages);
    component.setState(result);
  };

},{"../components/profile":8,"../flux/actions/chat/ExampleActionCreators":9,"../flux/stores/chat/MessageStore":12}],8:[function(require,module,exports){
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

 var dispatcher = require('../../dispatcher/datenodeDispatcher');
 var constants = require('../../constants/ActionConstants');
 var EventEmitter = require('events').EventEmitter;

var ActionTypes = constants.ActionTypes;

module.exports = {

  createTest: function() {
    dispatcher.dispatch({
      type: ActionTypes.TEST
    });
  }

};

},{"../../constants/ActionConstants":10,"../../dispatcher/datenodeDispatcher":11,"events":6}],10:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */


var keyMirror = require('keymirror');

module.exports = {

  /* typy akcí, které mohou nastat */
  ActionTypes: keyMirror({
    TEST : null
  })

};

},{"keymirror":4}],11:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

var Dispatcher = require('flux').Dispatcher;

module.exports = new Dispatcher();

},{"flux":1}],12:[function(require,module,exports){
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

var _messages = {};

var MessageStore = assign({}, EventEmitter.prototype, {

  emitChange: function() {
    this.emit(CHANGE_EVENT);
  },

  /**
   * @param {function} callback
   */
  addChangeListener: function(callback) {
    this.on(CHANGE_EVENT, callback);
  },

  removeChangeListener: function(callback) {
    this.removeListener(CHANGE_EVENT, callback);
  },

  get: function(id) {
    return _messages[id];
  },

  getAll: function() {
    return _messages;
  }

});

MessageStore.dispatchToken = Dispatcher.register(function(action) {
  console.log('It works!');
  MessageStore.emitChange();
});

module.exports = MessageStore;

},{"../../constants/ActionConstants":10,"../../dispatcher/datenodeDispatcher":11,"events":6,"object-assign":5}],13:[function(require,module,exports){
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

},{"./chat/reactChat":7}]},{},[13])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwibm9kZV9tb2R1bGVzL2ZsdXgvaW5kZXguanMiLCJub2RlX21vZHVsZXMvZmx1eC9saWIvRGlzcGF0Y2hlci5qcyIsIm5vZGVfbW9kdWxlcy9mbHV4L2xpYi9pbnZhcmlhbnQuanMiLCJub2RlX21vZHVsZXMva2V5bWlycm9yL2luZGV4LmpzIiwibm9kZV9tb2R1bGVzL29iamVjdC1hc3NpZ24vaW5kZXguanMiLCJub2RlX21vZHVsZXMvd2F0Y2hpZnkvbm9kZV9tb2R1bGVzL2Jyb3dzZXJpZnkvbm9kZV9tb2R1bGVzL2V2ZW50cy9ldmVudHMuanMiLCJzcmMvY2hhdC9yZWFjdENoYXQuanMiLCJzcmMvY29tcG9uZW50cy9wcm9maWxlLmpzIiwic3JjL2ZsdXgvYWN0aW9ucy9jaGF0L0V4YW1wbGVBY3Rpb25DcmVhdG9ycy5qcyIsInNyYy9mbHV4L2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMuanMiLCJzcmMvZmx1eC9kaXNwYXRjaGVyL2RhdGVub2RlRGlzcGF0Y2hlci5qcyIsInNyYy9mbHV4L3N0b3Jlcy9jaGF0L01lc3NhZ2VTdG9yZS5qcyIsInNyYy9yZWFjdERhdGVub2RlLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNWQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzFQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNyREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNyQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM3U0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pPQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzNCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2hCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdERBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8qKlxyXG4gKiBDb3B5cmlnaHQgKGMpIDIwMTQtMjAxNSwgRmFjZWJvb2ssIEluYy5cclxuICogQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICpcclxuICogVGhpcyBzb3VyY2UgY29kZSBpcyBsaWNlbnNlZCB1bmRlciB0aGUgQlNELXN0eWxlIGxpY2Vuc2UgZm91bmQgaW4gdGhlXHJcbiAqIExJQ0VOU0UgZmlsZSBpbiB0aGUgcm9vdCBkaXJlY3Rvcnkgb2YgdGhpcyBzb3VyY2UgdHJlZS4gQW4gYWRkaXRpb25hbCBncmFudFxyXG4gKiBvZiBwYXRlbnQgcmlnaHRzIGNhbiBiZSBmb3VuZCBpbiB0aGUgUEFURU5UUyBmaWxlIGluIHRoZSBzYW1lIGRpcmVjdG9yeS5cclxuICovXHJcblxyXG5tb2R1bGUuZXhwb3J0cy5EaXNwYXRjaGVyID0gcmVxdWlyZSgnLi9saWIvRGlzcGF0Y2hlcicpXHJcbiIsIi8qXHJcbiAqIENvcHlyaWdodCAoYykgMjAxNCwgRmFjZWJvb2ssIEluYy5cclxuICogQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICpcclxuICogVGhpcyBzb3VyY2UgY29kZSBpcyBsaWNlbnNlZCB1bmRlciB0aGUgQlNELXN0eWxlIGxpY2Vuc2UgZm91bmQgaW4gdGhlXHJcbiAqIExJQ0VOU0UgZmlsZSBpbiB0aGUgcm9vdCBkaXJlY3Rvcnkgb2YgdGhpcyBzb3VyY2UgdHJlZS4gQW4gYWRkaXRpb25hbCBncmFudFxyXG4gKiBvZiBwYXRlbnQgcmlnaHRzIGNhbiBiZSBmb3VuZCBpbiB0aGUgUEFURU5UUyBmaWxlIGluIHRoZSBzYW1lIGRpcmVjdG9yeS5cclxuICpcclxuICogQHByb3ZpZGVzTW9kdWxlIERpc3BhdGNoZXJcclxuICogQHR5cGVjaGVja3NcclxuICovXHJcblxyXG5cInVzZSBzdHJpY3RcIjtcclxuXHJcbnZhciBpbnZhcmlhbnQgPSByZXF1aXJlKCcuL2ludmFyaWFudCcpO1xyXG5cclxudmFyIF9sYXN0SUQgPSAxO1xyXG52YXIgX3ByZWZpeCA9ICdJRF8nO1xyXG5cclxuLyoqXHJcbiAqIERpc3BhdGNoZXIgaXMgdXNlZCB0byBicm9hZGNhc3QgcGF5bG9hZHMgdG8gcmVnaXN0ZXJlZCBjYWxsYmFja3MuIFRoaXMgaXNcclxuICogZGlmZmVyZW50IGZyb20gZ2VuZXJpYyBwdWItc3ViIHN5c3RlbXMgaW4gdHdvIHdheXM6XHJcbiAqXHJcbiAqICAgMSkgQ2FsbGJhY2tzIGFyZSBub3Qgc3Vic2NyaWJlZCB0byBwYXJ0aWN1bGFyIGV2ZW50cy4gRXZlcnkgcGF5bG9hZCBpc1xyXG4gKiAgICAgIGRpc3BhdGNoZWQgdG8gZXZlcnkgcmVnaXN0ZXJlZCBjYWxsYmFjay5cclxuICogICAyKSBDYWxsYmFja3MgY2FuIGJlIGRlZmVycmVkIGluIHdob2xlIG9yIHBhcnQgdW50aWwgb3RoZXIgY2FsbGJhY2tzIGhhdmVcclxuICogICAgICBiZWVuIGV4ZWN1dGVkLlxyXG4gKlxyXG4gKiBGb3IgZXhhbXBsZSwgY29uc2lkZXIgdGhpcyBoeXBvdGhldGljYWwgZmxpZ2h0IGRlc3RpbmF0aW9uIGZvcm0sIHdoaWNoXHJcbiAqIHNlbGVjdHMgYSBkZWZhdWx0IGNpdHkgd2hlbiBhIGNvdW50cnkgaXMgc2VsZWN0ZWQ6XHJcbiAqXHJcbiAqICAgdmFyIGZsaWdodERpc3BhdGNoZXIgPSBuZXcgRGlzcGF0Y2hlcigpO1xyXG4gKlxyXG4gKiAgIC8vIEtlZXBzIHRyYWNrIG9mIHdoaWNoIGNvdW50cnkgaXMgc2VsZWN0ZWRcclxuICogICB2YXIgQ291bnRyeVN0b3JlID0ge2NvdW50cnk6IG51bGx9O1xyXG4gKlxyXG4gKiAgIC8vIEtlZXBzIHRyYWNrIG9mIHdoaWNoIGNpdHkgaXMgc2VsZWN0ZWRcclxuICogICB2YXIgQ2l0eVN0b3JlID0ge2NpdHk6IG51bGx9O1xyXG4gKlxyXG4gKiAgIC8vIEtlZXBzIHRyYWNrIG9mIHRoZSBiYXNlIGZsaWdodCBwcmljZSBvZiB0aGUgc2VsZWN0ZWQgY2l0eVxyXG4gKiAgIHZhciBGbGlnaHRQcmljZVN0b3JlID0ge3ByaWNlOiBudWxsfVxyXG4gKlxyXG4gKiBXaGVuIGEgdXNlciBjaGFuZ2VzIHRoZSBzZWxlY3RlZCBjaXR5LCB3ZSBkaXNwYXRjaCB0aGUgcGF5bG9hZDpcclxuICpcclxuICogICBmbGlnaHREaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICogICAgIGFjdGlvblR5cGU6ICdjaXR5LXVwZGF0ZScsXHJcbiAqICAgICBzZWxlY3RlZENpdHk6ICdwYXJpcydcclxuICogICB9KTtcclxuICpcclxuICogVGhpcyBwYXlsb2FkIGlzIGRpZ2VzdGVkIGJ5IGBDaXR5U3RvcmVgOlxyXG4gKlxyXG4gKiAgIGZsaWdodERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gKiAgICAgaWYgKHBheWxvYWQuYWN0aW9uVHlwZSA9PT0gJ2NpdHktdXBkYXRlJykge1xyXG4gKiAgICAgICBDaXR5U3RvcmUuY2l0eSA9IHBheWxvYWQuc2VsZWN0ZWRDaXR5O1xyXG4gKiAgICAgfVxyXG4gKiAgIH0pO1xyXG4gKlxyXG4gKiBXaGVuIHRoZSB1c2VyIHNlbGVjdHMgYSBjb3VudHJ5LCB3ZSBkaXNwYXRjaCB0aGUgcGF5bG9hZDpcclxuICpcclxuICogICBmbGlnaHREaXNwYXRjaGVyLmRpc3BhdGNoKHtcclxuICogICAgIGFjdGlvblR5cGU6ICdjb3VudHJ5LXVwZGF0ZScsXHJcbiAqICAgICBzZWxlY3RlZENvdW50cnk6ICdhdXN0cmFsaWEnXHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoaXMgcGF5bG9hZCBpcyBkaWdlc3RlZCBieSBib3RoIHN0b3JlczpcclxuICpcclxuICogICAgQ291bnRyeVN0b3JlLmRpc3BhdGNoVG9rZW4gPSBmbGlnaHREaXNwYXRjaGVyLnJlZ2lzdGVyKGZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICogICAgIGlmIChwYXlsb2FkLmFjdGlvblR5cGUgPT09ICdjb3VudHJ5LXVwZGF0ZScpIHtcclxuICogICAgICAgQ291bnRyeVN0b3JlLmNvdW50cnkgPSBwYXlsb2FkLnNlbGVjdGVkQ291bnRyeTtcclxuICogICAgIH1cclxuICogICB9KTtcclxuICpcclxuICogV2hlbiB0aGUgY2FsbGJhY2sgdG8gdXBkYXRlIGBDb3VudHJ5U3RvcmVgIGlzIHJlZ2lzdGVyZWQsIHdlIHNhdmUgYSByZWZlcmVuY2VcclxuICogdG8gdGhlIHJldHVybmVkIHRva2VuLiBVc2luZyB0aGlzIHRva2VuIHdpdGggYHdhaXRGb3IoKWAsIHdlIGNhbiBndWFyYW50ZWVcclxuICogdGhhdCBgQ291bnRyeVN0b3JlYCBpcyB1cGRhdGVkIGJlZm9yZSB0aGUgY2FsbGJhY2sgdGhhdCB1cGRhdGVzIGBDaXR5U3RvcmVgXHJcbiAqIG5lZWRzIHRvIHF1ZXJ5IGl0cyBkYXRhLlxyXG4gKlxyXG4gKiAgIENpdHlTdG9yZS5kaXNwYXRjaFRva2VuID0gZmxpZ2h0RGlzcGF0Y2hlci5yZWdpc3RlcihmdW5jdGlvbihwYXlsb2FkKSB7XHJcbiAqICAgICBpZiAocGF5bG9hZC5hY3Rpb25UeXBlID09PSAnY291bnRyeS11cGRhdGUnKSB7XHJcbiAqICAgICAgIC8vIGBDb3VudHJ5U3RvcmUuY291bnRyeWAgbWF5IG5vdCBiZSB1cGRhdGVkLlxyXG4gKiAgICAgICBmbGlnaHREaXNwYXRjaGVyLndhaXRGb3IoW0NvdW50cnlTdG9yZS5kaXNwYXRjaFRva2VuXSk7XHJcbiAqICAgICAgIC8vIGBDb3VudHJ5U3RvcmUuY291bnRyeWAgaXMgbm93IGd1YXJhbnRlZWQgdG8gYmUgdXBkYXRlZC5cclxuICpcclxuICogICAgICAgLy8gU2VsZWN0IHRoZSBkZWZhdWx0IGNpdHkgZm9yIHRoZSBuZXcgY291bnRyeVxyXG4gKiAgICAgICBDaXR5U3RvcmUuY2l0eSA9IGdldERlZmF1bHRDaXR5Rm9yQ291bnRyeShDb3VudHJ5U3RvcmUuY291bnRyeSk7XHJcbiAqICAgICB9XHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoZSB1c2FnZSBvZiBgd2FpdEZvcigpYCBjYW4gYmUgY2hhaW5lZCwgZm9yIGV4YW1wbGU6XHJcbiAqXHJcbiAqICAgRmxpZ2h0UHJpY2VTdG9yZS5kaXNwYXRjaFRva2VuID1cclxuICogICAgIGZsaWdodERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24ocGF5bG9hZCkge1xyXG4gKiAgICAgICBzd2l0Y2ggKHBheWxvYWQuYWN0aW9uVHlwZSkge1xyXG4gKiAgICAgICAgIGNhc2UgJ2NvdW50cnktdXBkYXRlJzpcclxuICogICAgICAgICAgIGZsaWdodERpc3BhdGNoZXIud2FpdEZvcihbQ2l0eVN0b3JlLmRpc3BhdGNoVG9rZW5dKTtcclxuICogICAgICAgICAgIEZsaWdodFByaWNlU3RvcmUucHJpY2UgPVxyXG4gKiAgICAgICAgICAgICBnZXRGbGlnaHRQcmljZVN0b3JlKENvdW50cnlTdG9yZS5jb3VudHJ5LCBDaXR5U3RvcmUuY2l0eSk7XHJcbiAqICAgICAgICAgICBicmVhaztcclxuICpcclxuICogICAgICAgICBjYXNlICdjaXR5LXVwZGF0ZSc6XHJcbiAqICAgICAgICAgICBGbGlnaHRQcmljZVN0b3JlLnByaWNlID1cclxuICogICAgICAgICAgICAgRmxpZ2h0UHJpY2VTdG9yZShDb3VudHJ5U3RvcmUuY291bnRyeSwgQ2l0eVN0b3JlLmNpdHkpO1xyXG4gKiAgICAgICAgICAgYnJlYWs7XHJcbiAqICAgICB9XHJcbiAqICAgfSk7XHJcbiAqXHJcbiAqIFRoZSBgY291bnRyeS11cGRhdGVgIHBheWxvYWQgd2lsbCBiZSBndWFyYW50ZWVkIHRvIGludm9rZSB0aGUgc3RvcmVzJ1xyXG4gKiByZWdpc3RlcmVkIGNhbGxiYWNrcyBpbiBvcmRlcjogYENvdW50cnlTdG9yZWAsIGBDaXR5U3RvcmVgLCB0aGVuXHJcbiAqIGBGbGlnaHRQcmljZVN0b3JlYC5cclxuICovXHJcblxyXG4gIGZ1bmN0aW9uIERpc3BhdGNoZXIoKSB7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrcyA9IHt9O1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmcgPSB7fTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNIYW5kbGVkID0ge307XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2lzRGlzcGF0Y2hpbmcgPSBmYWxzZTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQgPSBudWxsO1xyXG4gIH1cclxuXHJcbiAgLyoqXHJcbiAgICogUmVnaXN0ZXJzIGEgY2FsbGJhY2sgdG8gYmUgaW52b2tlZCB3aXRoIGV2ZXJ5IGRpc3BhdGNoZWQgcGF5bG9hZC4gUmV0dXJuc1xyXG4gICAqIGEgdG9rZW4gdGhhdCBjYW4gYmUgdXNlZCB3aXRoIGB3YWl0Rm9yKClgLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtmdW5jdGlvbn0gY2FsbGJhY2tcclxuICAgKiBAcmV0dXJuIHtzdHJpbmd9XHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUucmVnaXN0ZXI9ZnVuY3Rpb24oY2FsbGJhY2spIHtcclxuICAgIHZhciBpZCA9IF9wcmVmaXggKyBfbGFzdElEKys7XHJcbiAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0gPSBjYWxsYmFjaztcclxuICAgIHJldHVybiBpZDtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBSZW1vdmVzIGEgY2FsbGJhY2sgYmFzZWQgb24gaXRzIHRva2VuLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtzdHJpbmd9IGlkXHJcbiAgICovXHJcbiAgRGlzcGF0Y2hlci5wcm90b3R5cGUudW5yZWdpc3Rlcj1mdW5jdGlvbihpZCkge1xyXG4gICAgaW52YXJpYW50KFxyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0sXHJcbiAgICAgICdEaXNwYXRjaGVyLnVucmVnaXN0ZXIoLi4uKTogYCVzYCBkb2VzIG5vdCBtYXAgdG8gYSByZWdpc3RlcmVkIGNhbGxiYWNrLicsXHJcbiAgICAgIGlkXHJcbiAgICApO1xyXG4gICAgZGVsZXRlIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBXYWl0cyBmb3IgdGhlIGNhbGxiYWNrcyBzcGVjaWZpZWQgdG8gYmUgaW52b2tlZCBiZWZvcmUgY29udGludWluZyBleGVjdXRpb25cclxuICAgKiBvZiB0aGUgY3VycmVudCBjYWxsYmFjay4gVGhpcyBtZXRob2Qgc2hvdWxkIG9ubHkgYmUgdXNlZCBieSBhIGNhbGxiYWNrIGluXHJcbiAgICogcmVzcG9uc2UgdG8gYSBkaXNwYXRjaGVkIHBheWxvYWQuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge2FycmF5PHN0cmluZz59IGlkc1xyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLndhaXRGb3I9ZnVuY3Rpb24oaWRzKSB7XHJcbiAgICBpbnZhcmlhbnQoXHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyxcclxuICAgICAgJ0Rpc3BhdGNoZXIud2FpdEZvciguLi4pOiBNdXN0IGJlIGludm9rZWQgd2hpbGUgZGlzcGF0Y2hpbmcuJ1xyXG4gICAgKTtcclxuICAgIGZvciAodmFyIGlpID0gMDsgaWkgPCBpZHMubGVuZ3RoOyBpaSsrKSB7XHJcbiAgICAgIHZhciBpZCA9IGlkc1tpaV07XHJcbiAgICAgIGlmICh0aGlzLiREaXNwYXRjaGVyX2lzUGVuZGluZ1tpZF0pIHtcclxuICAgICAgICBpbnZhcmlhbnQoXHJcbiAgICAgICAgICB0aGlzLiREaXNwYXRjaGVyX2lzSGFuZGxlZFtpZF0sXHJcbiAgICAgICAgICAnRGlzcGF0Y2hlci53YWl0Rm9yKC4uLik6IENpcmN1bGFyIGRlcGVuZGVuY3kgZGV0ZWN0ZWQgd2hpbGUgJyArXHJcbiAgICAgICAgICAnd2FpdGluZyBmb3IgYCVzYC4nLFxyXG4gICAgICAgICAgaWRcclxuICAgICAgICApO1xyXG4gICAgICAgIGNvbnRpbnVlO1xyXG4gICAgICB9XHJcbiAgICAgIGludmFyaWFudChcclxuICAgICAgICB0aGlzLiREaXNwYXRjaGVyX2NhbGxiYWNrc1tpZF0sXHJcbiAgICAgICAgJ0Rpc3BhdGNoZXIud2FpdEZvciguLi4pOiBgJXNgIGRvZXMgbm90IG1hcCB0byBhIHJlZ2lzdGVyZWQgY2FsbGJhY2suJyxcclxuICAgICAgICBpZFxyXG4gICAgICApO1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2ludm9rZUNhbGxiYWNrKGlkKTtcclxuICAgIH1cclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBEaXNwYXRjaGVzIGEgcGF5bG9hZCB0byBhbGwgcmVnaXN0ZXJlZCBjYWxsYmFja3MuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge29iamVjdH0gcGF5bG9hZFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLmRpc3BhdGNoPWZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICAgIGludmFyaWFudChcclxuICAgICAgIXRoaXMuJERpc3BhdGNoZXJfaXNEaXNwYXRjaGluZyxcclxuICAgICAgJ0Rpc3BhdGNoLmRpc3BhdGNoKC4uLik6IENhbm5vdCBkaXNwYXRjaCBpbiB0aGUgbWlkZGxlIG9mIGEgZGlzcGF0Y2guJ1xyXG4gICAgKTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfc3RhcnREaXNwYXRjaGluZyhwYXlsb2FkKTtcclxuICAgIHRyeSB7XHJcbiAgICAgIGZvciAodmFyIGlkIGluIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzKSB7XHJcbiAgICAgICAgaWYgKHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nW2lkXSkge1xyXG4gICAgICAgICAgY29udGludWU7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHRoaXMuJERpc3BhdGNoZXJfaW52b2tlQ2FsbGJhY2soaWQpO1xyXG4gICAgICB9XHJcbiAgICB9IGZpbmFsbHkge1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX3N0b3BEaXNwYXRjaGluZygpO1xyXG4gICAgfVxyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIElzIHRoaXMgRGlzcGF0Y2hlciBjdXJyZW50bHkgZGlzcGF0Y2hpbmcuXHJcbiAgICpcclxuICAgKiBAcmV0dXJuIHtib29sZWFufVxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLmlzRGlzcGF0Y2hpbmc9ZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4gdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIENhbGwgdGhlIGNhbGxiYWNrIHN0b3JlZCB3aXRoIHRoZSBnaXZlbiBpZC4gQWxzbyBkbyBzb21lIGludGVybmFsXHJcbiAgICogYm9va2tlZXBpbmcuXHJcbiAgICpcclxuICAgKiBAcGFyYW0ge3N0cmluZ30gaWRcclxuICAgKiBAaW50ZXJuYWxcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS4kRGlzcGF0Y2hlcl9pbnZva2VDYWxsYmFjaz1mdW5jdGlvbihpZCkge1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc1BlbmRpbmdbaWRdID0gdHJ1ZTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzW2lkXSh0aGlzLiREaXNwYXRjaGVyX3BlbmRpbmdQYXlsb2FkKTtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfaXNIYW5kbGVkW2lkXSA9IHRydWU7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogU2V0IHVwIGJvb2trZWVwaW5nIG5lZWRlZCB3aGVuIGRpc3BhdGNoaW5nLlxyXG4gICAqXHJcbiAgICogQHBhcmFtIHtvYmplY3R9IHBheWxvYWRcclxuICAgKiBAaW50ZXJuYWxcclxuICAgKi9cclxuICBEaXNwYXRjaGVyLnByb3RvdHlwZS4kRGlzcGF0Y2hlcl9zdGFydERpc3BhdGNoaW5nPWZ1bmN0aW9uKHBheWxvYWQpIHtcclxuICAgIGZvciAodmFyIGlkIGluIHRoaXMuJERpc3BhdGNoZXJfY2FsbGJhY2tzKSB7XHJcbiAgICAgIHRoaXMuJERpc3BhdGNoZXJfaXNQZW5kaW5nW2lkXSA9IGZhbHNlO1xyXG4gICAgICB0aGlzLiREaXNwYXRjaGVyX2lzSGFuZGxlZFtpZF0gPSBmYWxzZTtcclxuICAgIH1cclxuICAgIHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQgPSBwYXlsb2FkO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nID0gdHJ1ZTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBDbGVhciBib29ra2VlcGluZyB1c2VkIGZvciBkaXNwYXRjaGluZy5cclxuICAgKlxyXG4gICAqIEBpbnRlcm5hbFxyXG4gICAqL1xyXG4gIERpc3BhdGNoZXIucHJvdG90eXBlLiREaXNwYXRjaGVyX3N0b3BEaXNwYXRjaGluZz1mdW5jdGlvbigpIHtcclxuICAgIHRoaXMuJERpc3BhdGNoZXJfcGVuZGluZ1BheWxvYWQgPSBudWxsO1xyXG4gICAgdGhpcy4kRGlzcGF0Y2hlcl9pc0Rpc3BhdGNoaW5nID0gZmFsc2U7XHJcbiAgfTtcclxuXHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IERpc3BhdGNoZXI7XHJcbiIsIi8qKlxyXG4gKiBDb3B5cmlnaHQgKGMpIDIwMTQsIEZhY2Vib29rLCBJbmMuXHJcbiAqIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqXHJcbiAqIFRoaXMgc291cmNlIGNvZGUgaXMgbGljZW5zZWQgdW5kZXIgdGhlIEJTRC1zdHlsZSBsaWNlbnNlIGZvdW5kIGluIHRoZVxyXG4gKiBMSUNFTlNFIGZpbGUgaW4gdGhlIHJvb3QgZGlyZWN0b3J5IG9mIHRoaXMgc291cmNlIHRyZWUuIEFuIGFkZGl0aW9uYWwgZ3JhbnRcclxuICogb2YgcGF0ZW50IHJpZ2h0cyBjYW4gYmUgZm91bmQgaW4gdGhlIFBBVEVOVFMgZmlsZSBpbiB0aGUgc2FtZSBkaXJlY3RvcnkuXHJcbiAqXHJcbiAqIEBwcm92aWRlc01vZHVsZSBpbnZhcmlhbnRcclxuICovXHJcblxyXG5cInVzZSBzdHJpY3RcIjtcclxuXHJcbi8qKlxyXG4gKiBVc2UgaW52YXJpYW50KCkgdG8gYXNzZXJ0IHN0YXRlIHdoaWNoIHlvdXIgcHJvZ3JhbSBhc3N1bWVzIHRvIGJlIHRydWUuXHJcbiAqXHJcbiAqIFByb3ZpZGUgc3ByaW50Zi1zdHlsZSBmb3JtYXQgKG9ubHkgJXMgaXMgc3VwcG9ydGVkKSBhbmQgYXJndW1lbnRzXHJcbiAqIHRvIHByb3ZpZGUgaW5mb3JtYXRpb24gYWJvdXQgd2hhdCBicm9rZSBhbmQgd2hhdCB5b3Ugd2VyZVxyXG4gKiBleHBlY3RpbmcuXHJcbiAqXHJcbiAqIFRoZSBpbnZhcmlhbnQgbWVzc2FnZSB3aWxsIGJlIHN0cmlwcGVkIGluIHByb2R1Y3Rpb24sIGJ1dCB0aGUgaW52YXJpYW50XHJcbiAqIHdpbGwgcmVtYWluIHRvIGVuc3VyZSBsb2dpYyBkb2VzIG5vdCBkaWZmZXIgaW4gcHJvZHVjdGlvbi5cclxuICovXHJcblxyXG52YXIgaW52YXJpYW50ID0gZnVuY3Rpb24oY29uZGl0aW9uLCBmb3JtYXQsIGEsIGIsIGMsIGQsIGUsIGYpIHtcclxuICBpZiAoZmFsc2UpIHtcclxuICAgIGlmIChmb3JtYXQgPT09IHVuZGVmaW5lZCkge1xyXG4gICAgICB0aHJvdyBuZXcgRXJyb3IoJ2ludmFyaWFudCByZXF1aXJlcyBhbiBlcnJvciBtZXNzYWdlIGFyZ3VtZW50Jyk7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICBpZiAoIWNvbmRpdGlvbikge1xyXG4gICAgdmFyIGVycm9yO1xyXG4gICAgaWYgKGZvcm1hdCA9PT0gdW5kZWZpbmVkKSB7XHJcbiAgICAgIGVycm9yID0gbmV3IEVycm9yKFxyXG4gICAgICAgICdNaW5pZmllZCBleGNlcHRpb24gb2NjdXJyZWQ7IHVzZSB0aGUgbm9uLW1pbmlmaWVkIGRldiBlbnZpcm9ubWVudCAnICtcclxuICAgICAgICAnZm9yIHRoZSBmdWxsIGVycm9yIG1lc3NhZ2UgYW5kIGFkZGl0aW9uYWwgaGVscGZ1bCB3YXJuaW5ncy4nXHJcbiAgICAgICk7XHJcbiAgICB9IGVsc2Uge1xyXG4gICAgICB2YXIgYXJncyA9IFthLCBiLCBjLCBkLCBlLCBmXTtcclxuICAgICAgdmFyIGFyZ0luZGV4ID0gMDtcclxuICAgICAgZXJyb3IgPSBuZXcgRXJyb3IoXHJcbiAgICAgICAgJ0ludmFyaWFudCBWaW9sYXRpb246ICcgK1xyXG4gICAgICAgIGZvcm1hdC5yZXBsYWNlKC8lcy9nLCBmdW5jdGlvbigpIHsgcmV0dXJuIGFyZ3NbYXJnSW5kZXgrK107IH0pXHJcbiAgICAgICk7XHJcbiAgICB9XHJcblxyXG4gICAgZXJyb3IuZnJhbWVzVG9Qb3AgPSAxOyAvLyB3ZSBkb24ndCBjYXJlIGFib3V0IGludmFyaWFudCdzIG93biBmcmFtZVxyXG4gICAgdGhyb3cgZXJyb3I7XHJcbiAgfVxyXG59O1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBpbnZhcmlhbnQ7XHJcbiIsIi8qKlxyXG4gKiBDb3B5cmlnaHQgMjAxMy0yMDE0IEZhY2Vib29rLCBJbmMuXHJcbiAqXHJcbiAqIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5zZSwgVmVyc2lvbiAyLjAgKHRoZSBcIkxpY2Vuc2VcIik7XHJcbiAqIHlvdSBtYXkgbm90IHVzZSB0aGlzIGZpbGUgZXhjZXB0IGluIGNvbXBsaWFuY2Ugd2l0aCB0aGUgTGljZW5zZS5cclxuICogWW91IG1heSBvYnRhaW4gYSBjb3B5IG9mIHRoZSBMaWNlbnNlIGF0XHJcbiAqXHJcbiAqIGh0dHA6Ly93d3cuYXBhY2hlLm9yZy9saWNlbnNlcy9MSUNFTlNFLTIuMFxyXG4gKlxyXG4gKiBVbmxlc3MgcmVxdWlyZWQgYnkgYXBwbGljYWJsZSBsYXcgb3IgYWdyZWVkIHRvIGluIHdyaXRpbmcsIHNvZnR3YXJlXHJcbiAqIGRpc3RyaWJ1dGVkIHVuZGVyIHRoZSBMaWNlbnNlIGlzIGRpc3RyaWJ1dGVkIG9uIGFuIFwiQVMgSVNcIiBCQVNJUyxcclxuICogV0lUSE9VVCBXQVJSQU5USUVTIE9SIENPTkRJVElPTlMgT0YgQU5ZIEtJTkQsIGVpdGhlciBleHByZXNzIG9yIGltcGxpZWQuXHJcbiAqIFNlZSB0aGUgTGljZW5zZSBmb3IgdGhlIHNwZWNpZmljIGxhbmd1YWdlIGdvdmVybmluZyBwZXJtaXNzaW9ucyBhbmRcclxuICogbGltaXRhdGlvbnMgdW5kZXIgdGhlIExpY2Vuc2UuXHJcbiAqXHJcbiAqL1xyXG5cclxuXCJ1c2Ugc3RyaWN0XCI7XHJcblxyXG4vKipcclxuICogQ29uc3RydWN0cyBhbiBlbnVtZXJhdGlvbiB3aXRoIGtleXMgZXF1YWwgdG8gdGhlaXIgdmFsdWUuXHJcbiAqXHJcbiAqIEZvciBleGFtcGxlOlxyXG4gKlxyXG4gKiAgIHZhciBDT0xPUlMgPSBrZXlNaXJyb3Ioe2JsdWU6IG51bGwsIHJlZDogbnVsbH0pO1xyXG4gKiAgIHZhciBteUNvbG9yID0gQ09MT1JTLmJsdWU7XHJcbiAqICAgdmFyIGlzQ29sb3JWYWxpZCA9ICEhQ09MT1JTW215Q29sb3JdO1xyXG4gKlxyXG4gKiBUaGUgbGFzdCBsaW5lIGNvdWxkIG5vdCBiZSBwZXJmb3JtZWQgaWYgdGhlIHZhbHVlcyBvZiB0aGUgZ2VuZXJhdGVkIGVudW0gd2VyZVxyXG4gKiBub3QgZXF1YWwgdG8gdGhlaXIga2V5cy5cclxuICpcclxuICogICBJbnB1dDogIHtrZXkxOiB2YWwxLCBrZXkyOiB2YWwyfVxyXG4gKiAgIE91dHB1dDoge2tleTE6IGtleTEsIGtleTI6IGtleTJ9XHJcbiAqXHJcbiAqIEBwYXJhbSB7b2JqZWN0fSBvYmpcclxuICogQHJldHVybiB7b2JqZWN0fVxyXG4gKi9cclxudmFyIGtleU1pcnJvciA9IGZ1bmN0aW9uKG9iaikge1xyXG4gIHZhciByZXQgPSB7fTtcclxuICB2YXIga2V5O1xyXG4gIGlmICghKG9iaiBpbnN0YW5jZW9mIE9iamVjdCAmJiAhQXJyYXkuaXNBcnJheShvYmopKSkge1xyXG4gICAgdGhyb3cgbmV3IEVycm9yKCdrZXlNaXJyb3IoLi4uKTogQXJndW1lbnQgbXVzdCBiZSBhbiBvYmplY3QuJyk7XHJcbiAgfVxyXG4gIGZvciAoa2V5IGluIG9iaikge1xyXG4gICAgaWYgKCFvYmouaGFzT3duUHJvcGVydHkoa2V5KSkge1xyXG4gICAgICBjb250aW51ZTtcclxuICAgIH1cclxuICAgIHJldFtrZXldID0ga2V5O1xyXG4gIH1cclxuICByZXR1cm4gcmV0O1xyXG59O1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBrZXlNaXJyb3I7XHJcbiIsIid1c2Ugc3RyaWN0JztcclxuXHJcbmZ1bmN0aW9uIFRvT2JqZWN0KHZhbCkge1xyXG5cdGlmICh2YWwgPT0gbnVsbCkge1xyXG5cdFx0dGhyb3cgbmV3IFR5cGVFcnJvcignT2JqZWN0LmFzc2lnbiBjYW5ub3QgYmUgY2FsbGVkIHdpdGggbnVsbCBvciB1bmRlZmluZWQnKTtcclxuXHR9XHJcblxyXG5cdHJldHVybiBPYmplY3QodmFsKTtcclxufVxyXG5cclxubW9kdWxlLmV4cG9ydHMgPSBPYmplY3QuYXNzaWduIHx8IGZ1bmN0aW9uICh0YXJnZXQsIHNvdXJjZSkge1xyXG5cdHZhciBwZW5kaW5nRXhjZXB0aW9uO1xyXG5cdHZhciBmcm9tO1xyXG5cdHZhciBrZXlzO1xyXG5cdHZhciB0byA9IFRvT2JqZWN0KHRhcmdldCk7XHJcblxyXG5cdGZvciAodmFyIHMgPSAxOyBzIDwgYXJndW1lbnRzLmxlbmd0aDsgcysrKSB7XHJcblx0XHRmcm9tID0gYXJndW1lbnRzW3NdO1xyXG5cdFx0a2V5cyA9IE9iamVjdC5rZXlzKE9iamVjdChmcm9tKSk7XHJcblxyXG5cdFx0Zm9yICh2YXIgaSA9IDA7IGkgPCBrZXlzLmxlbmd0aDsgaSsrKSB7XHJcblx0XHRcdHRyeSB7XHJcblx0XHRcdFx0dG9ba2V5c1tpXV0gPSBmcm9tW2tleXNbaV1dO1xyXG5cdFx0XHR9IGNhdGNoIChlcnIpIHtcclxuXHRcdFx0XHRpZiAocGVuZGluZ0V4Y2VwdGlvbiA9PT0gdW5kZWZpbmVkKSB7XHJcblx0XHRcdFx0XHRwZW5kaW5nRXhjZXB0aW9uID0gZXJyO1xyXG5cdFx0XHRcdH1cclxuXHRcdFx0fVxyXG5cdFx0fVxyXG5cdH1cclxuXHJcblx0aWYgKHBlbmRpbmdFeGNlcHRpb24pIHtcclxuXHRcdHRocm93IHBlbmRpbmdFeGNlcHRpb247XHJcblx0fVxyXG5cclxuXHRyZXR1cm4gdG87XHJcbn07XHJcbiIsIi8vIENvcHlyaWdodCBKb3llbnQsIEluYy4gYW5kIG90aGVyIE5vZGUgY29udHJpYnV0b3JzLlxyXG4vL1xyXG4vLyBQZXJtaXNzaW9uIGlzIGhlcmVieSBncmFudGVkLCBmcmVlIG9mIGNoYXJnZSwgdG8gYW55IHBlcnNvbiBvYnRhaW5pbmcgYVxyXG4vLyBjb3B5IG9mIHRoaXMgc29mdHdhcmUgYW5kIGFzc29jaWF0ZWQgZG9jdW1lbnRhdGlvbiBmaWxlcyAodGhlXHJcbi8vIFwiU29mdHdhcmVcIiksIHRvIGRlYWwgaW4gdGhlIFNvZnR3YXJlIHdpdGhvdXQgcmVzdHJpY3Rpb24sIGluY2x1ZGluZ1xyXG4vLyB3aXRob3V0IGxpbWl0YXRpb24gdGhlIHJpZ2h0cyB0byB1c2UsIGNvcHksIG1vZGlmeSwgbWVyZ2UsIHB1Ymxpc2gsXHJcbi8vIGRpc3RyaWJ1dGUsIHN1YmxpY2Vuc2UsIGFuZC9vciBzZWxsIGNvcGllcyBvZiB0aGUgU29mdHdhcmUsIGFuZCB0byBwZXJtaXRcclxuLy8gcGVyc29ucyB0byB3aG9tIHRoZSBTb2Z0d2FyZSBpcyBmdXJuaXNoZWQgdG8gZG8gc28sIHN1YmplY3QgdG8gdGhlXHJcbi8vIGZvbGxvd2luZyBjb25kaXRpb25zOlxyXG4vL1xyXG4vLyBUaGUgYWJvdmUgY29weXJpZ2h0IG5vdGljZSBhbmQgdGhpcyBwZXJtaXNzaW9uIG5vdGljZSBzaGFsbCBiZSBpbmNsdWRlZFxyXG4vLyBpbiBhbGwgY29waWVzIG9yIHN1YnN0YW50aWFsIHBvcnRpb25zIG9mIHRoZSBTb2Z0d2FyZS5cclxuLy9cclxuLy8gVEhFIFNPRlRXQVJFIElTIFBST1ZJREVEIFwiQVMgSVNcIiwgV0lUSE9VVCBXQVJSQU5UWSBPRiBBTlkgS0lORCwgRVhQUkVTU1xyXG4vLyBPUiBJTVBMSUVELCBJTkNMVURJTkcgQlVUIE5PVCBMSU1JVEVEIFRPIFRIRSBXQVJSQU5USUVTIE9GXHJcbi8vIE1FUkNIQU5UQUJJTElUWSwgRklUTkVTUyBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UgQU5EIE5PTklORlJJTkdFTUVOVC4gSU5cclxuLy8gTk8gRVZFTlQgU0hBTEwgVEhFIEFVVEhPUlMgT1IgQ09QWVJJR0hUIEhPTERFUlMgQkUgTElBQkxFIEZPUiBBTlkgQ0xBSU0sXHJcbi8vIERBTUFHRVMgT1IgT1RIRVIgTElBQklMSVRZLCBXSEVUSEVSIElOIEFOIEFDVElPTiBPRiBDT05UUkFDVCwgVE9SVCBPUlxyXG4vLyBPVEhFUldJU0UsIEFSSVNJTkcgRlJPTSwgT1VUIE9GIE9SIElOIENPTk5FQ1RJT04gV0lUSCBUSEUgU09GVFdBUkUgT1IgVEhFXHJcbi8vIFVTRSBPUiBPVEhFUiBERUFMSU5HUyBJTiBUSEUgU09GVFdBUkUuXHJcblxyXG5mdW5jdGlvbiBFdmVudEVtaXR0ZXIoKSB7XHJcbiAgdGhpcy5fZXZlbnRzID0gdGhpcy5fZXZlbnRzIHx8IHt9O1xyXG4gIHRoaXMuX21heExpc3RlbmVycyA9IHRoaXMuX21heExpc3RlbmVycyB8fCB1bmRlZmluZWQ7XHJcbn1cclxubW9kdWxlLmV4cG9ydHMgPSBFdmVudEVtaXR0ZXI7XHJcblxyXG4vLyBCYWNrd2FyZHMtY29tcGF0IHdpdGggbm9kZSAwLjEwLnhcclxuRXZlbnRFbWl0dGVyLkV2ZW50RW1pdHRlciA9IEV2ZW50RW1pdHRlcjtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuX2V2ZW50cyA9IHVuZGVmaW5lZDtcclxuRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5fbWF4TGlzdGVuZXJzID0gdW5kZWZpbmVkO1xyXG5cclxuLy8gQnkgZGVmYXVsdCBFdmVudEVtaXR0ZXJzIHdpbGwgcHJpbnQgYSB3YXJuaW5nIGlmIG1vcmUgdGhhbiAxMCBsaXN0ZW5lcnMgYXJlXHJcbi8vIGFkZGVkIHRvIGl0LiBUaGlzIGlzIGEgdXNlZnVsIGRlZmF1bHQgd2hpY2ggaGVscHMgZmluZGluZyBtZW1vcnkgbGVha3MuXHJcbkV2ZW50RW1pdHRlci5kZWZhdWx0TWF4TGlzdGVuZXJzID0gMTA7XHJcblxyXG4vLyBPYnZpb3VzbHkgbm90IGFsbCBFbWl0dGVycyBzaG91bGQgYmUgbGltaXRlZCB0byAxMC4gVGhpcyBmdW5jdGlvbiBhbGxvd3NcclxuLy8gdGhhdCB0byBiZSBpbmNyZWFzZWQuIFNldCB0byB6ZXJvIGZvciB1bmxpbWl0ZWQuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUuc2V0TWF4TGlzdGVuZXJzID0gZnVuY3Rpb24obikge1xyXG4gIGlmICghaXNOdW1iZXIobikgfHwgbiA8IDAgfHwgaXNOYU4obikpXHJcbiAgICB0aHJvdyBUeXBlRXJyb3IoJ24gbXVzdCBiZSBhIHBvc2l0aXZlIG51bWJlcicpO1xyXG4gIHRoaXMuX21heExpc3RlbmVycyA9IG47XHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLmVtaXQgPSBmdW5jdGlvbih0eXBlKSB7XHJcbiAgdmFyIGVyLCBoYW5kbGVyLCBsZW4sIGFyZ3MsIGksIGxpc3RlbmVycztcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHMpXHJcbiAgICB0aGlzLl9ldmVudHMgPSB7fTtcclxuXHJcbiAgLy8gSWYgdGhlcmUgaXMgbm8gJ2Vycm9yJyBldmVudCBsaXN0ZW5lciB0aGVuIHRocm93LlxyXG4gIGlmICh0eXBlID09PSAnZXJyb3InKSB7XHJcbiAgICBpZiAoIXRoaXMuX2V2ZW50cy5lcnJvciB8fFxyXG4gICAgICAgIChpc09iamVjdCh0aGlzLl9ldmVudHMuZXJyb3IpICYmICF0aGlzLl9ldmVudHMuZXJyb3IubGVuZ3RoKSkge1xyXG4gICAgICBlciA9IGFyZ3VtZW50c1sxXTtcclxuICAgICAgaWYgKGVyIGluc3RhbmNlb2YgRXJyb3IpIHtcclxuICAgICAgICB0aHJvdyBlcjsgLy8gVW5oYW5kbGVkICdlcnJvcicgZXZlbnRcclxuICAgICAgfVxyXG4gICAgICB0aHJvdyBUeXBlRXJyb3IoJ1VuY2F1Z2h0LCB1bnNwZWNpZmllZCBcImVycm9yXCIgZXZlbnQuJyk7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICBoYW5kbGVyID0gdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG5cclxuICBpZiAoaXNVbmRlZmluZWQoaGFuZGxlcikpXHJcbiAgICByZXR1cm4gZmFsc2U7XHJcblxyXG4gIGlmIChpc0Z1bmN0aW9uKGhhbmRsZXIpKSB7XHJcbiAgICBzd2l0Y2ggKGFyZ3VtZW50cy5sZW5ndGgpIHtcclxuICAgICAgLy8gZmFzdCBjYXNlc1xyXG4gICAgICBjYXNlIDE6XHJcbiAgICAgICAgaGFuZGxlci5jYWxsKHRoaXMpO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICBjYXNlIDI6XHJcbiAgICAgICAgaGFuZGxlci5jYWxsKHRoaXMsIGFyZ3VtZW50c1sxXSk7XHJcbiAgICAgICAgYnJlYWs7XHJcbiAgICAgIGNhc2UgMzpcclxuICAgICAgICBoYW5kbGVyLmNhbGwodGhpcywgYXJndW1lbnRzWzFdLCBhcmd1bWVudHNbMl0pO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICAvLyBzbG93ZXJcclxuICAgICAgZGVmYXVsdDpcclxuICAgICAgICBsZW4gPSBhcmd1bWVudHMubGVuZ3RoO1xyXG4gICAgICAgIGFyZ3MgPSBuZXcgQXJyYXkobGVuIC0gMSk7XHJcbiAgICAgICAgZm9yIChpID0gMTsgaSA8IGxlbjsgaSsrKVxyXG4gICAgICAgICAgYXJnc1tpIC0gMV0gPSBhcmd1bWVudHNbaV07XHJcbiAgICAgICAgaGFuZGxlci5hcHBseSh0aGlzLCBhcmdzKTtcclxuICAgIH1cclxuICB9IGVsc2UgaWYgKGlzT2JqZWN0KGhhbmRsZXIpKSB7XHJcbiAgICBsZW4gPSBhcmd1bWVudHMubGVuZ3RoO1xyXG4gICAgYXJncyA9IG5ldyBBcnJheShsZW4gLSAxKTtcclxuICAgIGZvciAoaSA9IDE7IGkgPCBsZW47IGkrKylcclxuICAgICAgYXJnc1tpIC0gMV0gPSBhcmd1bWVudHNbaV07XHJcblxyXG4gICAgbGlzdGVuZXJzID0gaGFuZGxlci5zbGljZSgpO1xyXG4gICAgbGVuID0gbGlzdGVuZXJzLmxlbmd0aDtcclxuICAgIGZvciAoaSA9IDA7IGkgPCBsZW47IGkrKylcclxuICAgICAgbGlzdGVuZXJzW2ldLmFwcGx5KHRoaXMsIGFyZ3MpO1xyXG4gIH1cclxuXHJcbiAgcmV0dXJuIHRydWU7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLmFkZExpc3RlbmVyID0gZnVuY3Rpb24odHlwZSwgbGlzdGVuZXIpIHtcclxuICB2YXIgbTtcclxuXHJcbiAgaWYgKCFpc0Z1bmN0aW9uKGxpc3RlbmVyKSlcclxuICAgIHRocm93IFR5cGVFcnJvcignbGlzdGVuZXIgbXVzdCBiZSBhIGZ1bmN0aW9uJyk7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzKVxyXG4gICAgdGhpcy5fZXZlbnRzID0ge307XHJcblxyXG4gIC8vIFRvIGF2b2lkIHJlY3Vyc2lvbiBpbiB0aGUgY2FzZSB0aGF0IHR5cGUgPT09IFwibmV3TGlzdGVuZXJcIiEgQmVmb3JlXHJcbiAgLy8gYWRkaW5nIGl0IHRvIHRoZSBsaXN0ZW5lcnMsIGZpcnN0IGVtaXQgXCJuZXdMaXN0ZW5lclwiLlxyXG4gIGlmICh0aGlzLl9ldmVudHMubmV3TGlzdGVuZXIpXHJcbiAgICB0aGlzLmVtaXQoJ25ld0xpc3RlbmVyJywgdHlwZSxcclxuICAgICAgICAgICAgICBpc0Z1bmN0aW9uKGxpc3RlbmVyLmxpc3RlbmVyKSA/XHJcbiAgICAgICAgICAgICAgbGlzdGVuZXIubGlzdGVuZXIgOiBsaXN0ZW5lcik7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzW3R5cGVdKVxyXG4gICAgLy8gT3B0aW1pemUgdGhlIGNhc2Ugb2Ygb25lIGxpc3RlbmVyLiBEb24ndCBuZWVkIHRoZSBleHRyYSBhcnJheSBvYmplY3QuXHJcbiAgICB0aGlzLl9ldmVudHNbdHlwZV0gPSBsaXN0ZW5lcjtcclxuICBlbHNlIGlmIChpc09iamVjdCh0aGlzLl9ldmVudHNbdHlwZV0pKVxyXG4gICAgLy8gSWYgd2UndmUgYWxyZWFkeSBnb3QgYW4gYXJyYXksIGp1c3QgYXBwZW5kLlxyXG4gICAgdGhpcy5fZXZlbnRzW3R5cGVdLnB1c2gobGlzdGVuZXIpO1xyXG4gIGVsc2VcclxuICAgIC8vIEFkZGluZyB0aGUgc2Vjb25kIGVsZW1lbnQsIG5lZWQgdG8gY2hhbmdlIHRvIGFycmF5LlxyXG4gICAgdGhpcy5fZXZlbnRzW3R5cGVdID0gW3RoaXMuX2V2ZW50c1t0eXBlXSwgbGlzdGVuZXJdO1xyXG5cclxuICAvLyBDaGVjayBmb3IgbGlzdGVuZXIgbGVha1xyXG4gIGlmIChpc09iamVjdCh0aGlzLl9ldmVudHNbdHlwZV0pICYmICF0aGlzLl9ldmVudHNbdHlwZV0ud2FybmVkKSB7XHJcbiAgICB2YXIgbTtcclxuICAgIGlmICghaXNVbmRlZmluZWQodGhpcy5fbWF4TGlzdGVuZXJzKSkge1xyXG4gICAgICBtID0gdGhpcy5fbWF4TGlzdGVuZXJzO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgbSA9IEV2ZW50RW1pdHRlci5kZWZhdWx0TWF4TGlzdGVuZXJzO1xyXG4gICAgfVxyXG5cclxuICAgIGlmIChtICYmIG0gPiAwICYmIHRoaXMuX2V2ZW50c1t0eXBlXS5sZW5ndGggPiBtKSB7XHJcbiAgICAgIHRoaXMuX2V2ZW50c1t0eXBlXS53YXJuZWQgPSB0cnVlO1xyXG4gICAgICBjb25zb2xlLmVycm9yKCcobm9kZSkgd2FybmluZzogcG9zc2libGUgRXZlbnRFbWl0dGVyIG1lbW9yeSAnICtcclxuICAgICAgICAgICAgICAgICAgICAnbGVhayBkZXRlY3RlZC4gJWQgbGlzdGVuZXJzIGFkZGVkLiAnICtcclxuICAgICAgICAgICAgICAgICAgICAnVXNlIGVtaXR0ZXIuc2V0TWF4TGlzdGVuZXJzKCkgdG8gaW5jcmVhc2UgbGltaXQuJyxcclxuICAgICAgICAgICAgICAgICAgICB0aGlzLl9ldmVudHNbdHlwZV0ubGVuZ3RoKTtcclxuICAgICAgaWYgKHR5cGVvZiBjb25zb2xlLnRyYWNlID09PSAnZnVuY3Rpb24nKSB7XHJcbiAgICAgICAgLy8gbm90IHN1cHBvcnRlZCBpbiBJRSAxMFxyXG4gICAgICAgIGNvbnNvbGUudHJhY2UoKTtcclxuICAgICAgfVxyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLm9uID0gRXZlbnRFbWl0dGVyLnByb3RvdHlwZS5hZGRMaXN0ZW5lcjtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUub25jZSA9IGZ1bmN0aW9uKHR5cGUsIGxpc3RlbmVyKSB7XHJcbiAgaWYgKCFpc0Z1bmN0aW9uKGxpc3RlbmVyKSlcclxuICAgIHRocm93IFR5cGVFcnJvcignbGlzdGVuZXIgbXVzdCBiZSBhIGZ1bmN0aW9uJyk7XHJcblxyXG4gIHZhciBmaXJlZCA9IGZhbHNlO1xyXG5cclxuICBmdW5jdGlvbiBnKCkge1xyXG4gICAgdGhpcy5yZW1vdmVMaXN0ZW5lcih0eXBlLCBnKTtcclxuXHJcbiAgICBpZiAoIWZpcmVkKSB7XHJcbiAgICAgIGZpcmVkID0gdHJ1ZTtcclxuICAgICAgbGlzdGVuZXIuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcclxuICAgIH1cclxuICB9XHJcblxyXG4gIGcubGlzdGVuZXIgPSBsaXN0ZW5lcjtcclxuICB0aGlzLm9uKHR5cGUsIGcpO1xyXG5cclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbi8vIGVtaXRzIGEgJ3JlbW92ZUxpc3RlbmVyJyBldmVudCBpZmYgdGhlIGxpc3RlbmVyIHdhcyByZW1vdmVkXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUucmVtb3ZlTGlzdGVuZXIgPSBmdW5jdGlvbih0eXBlLCBsaXN0ZW5lcikge1xyXG4gIHZhciBsaXN0LCBwb3NpdGlvbiwgbGVuZ3RoLCBpO1xyXG5cclxuICBpZiAoIWlzRnVuY3Rpb24obGlzdGVuZXIpKVxyXG4gICAgdGhyb3cgVHlwZUVycm9yKCdsaXN0ZW5lciBtdXN0IGJlIGEgZnVuY3Rpb24nKTtcclxuXHJcbiAgaWYgKCF0aGlzLl9ldmVudHMgfHwgIXRoaXMuX2V2ZW50c1t0eXBlXSlcclxuICAgIHJldHVybiB0aGlzO1xyXG5cclxuICBsaXN0ID0gdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG4gIGxlbmd0aCA9IGxpc3QubGVuZ3RoO1xyXG4gIHBvc2l0aW9uID0gLTE7XHJcblxyXG4gIGlmIChsaXN0ID09PSBsaXN0ZW5lciB8fFxyXG4gICAgICAoaXNGdW5jdGlvbihsaXN0Lmxpc3RlbmVyKSAmJiBsaXN0Lmxpc3RlbmVyID09PSBsaXN0ZW5lcikpIHtcclxuICAgIGRlbGV0ZSB0aGlzLl9ldmVudHNbdHlwZV07XHJcbiAgICBpZiAodGhpcy5fZXZlbnRzLnJlbW92ZUxpc3RlbmVyKVxyXG4gICAgICB0aGlzLmVtaXQoJ3JlbW92ZUxpc3RlbmVyJywgdHlwZSwgbGlzdGVuZXIpO1xyXG5cclxuICB9IGVsc2UgaWYgKGlzT2JqZWN0KGxpc3QpKSB7XHJcbiAgICBmb3IgKGkgPSBsZW5ndGg7IGktLSA+IDA7KSB7XHJcbiAgICAgIGlmIChsaXN0W2ldID09PSBsaXN0ZW5lciB8fFxyXG4gICAgICAgICAgKGxpc3RbaV0ubGlzdGVuZXIgJiYgbGlzdFtpXS5saXN0ZW5lciA9PT0gbGlzdGVuZXIpKSB7XHJcbiAgICAgICAgcG9zaXRpb24gPSBpO1xyXG4gICAgICAgIGJyZWFrO1xyXG4gICAgICB9XHJcbiAgICB9XHJcblxyXG4gICAgaWYgKHBvc2l0aW9uIDwgMClcclxuICAgICAgcmV0dXJuIHRoaXM7XHJcblxyXG4gICAgaWYgKGxpc3QubGVuZ3RoID09PSAxKSB7XHJcbiAgICAgIGxpc3QubGVuZ3RoID0gMDtcclxuICAgICAgZGVsZXRlIHRoaXMuX2V2ZW50c1t0eXBlXTtcclxuICAgIH0gZWxzZSB7XHJcbiAgICAgIGxpc3Quc3BsaWNlKHBvc2l0aW9uLCAxKTtcclxuICAgIH1cclxuXHJcbiAgICBpZiAodGhpcy5fZXZlbnRzLnJlbW92ZUxpc3RlbmVyKVxyXG4gICAgICB0aGlzLmVtaXQoJ3JlbW92ZUxpc3RlbmVyJywgdHlwZSwgbGlzdGVuZXIpO1xyXG4gIH1cclxuXHJcbiAgcmV0dXJuIHRoaXM7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIucHJvdG90eXBlLnJlbW92ZUFsbExpc3RlbmVycyA9IGZ1bmN0aW9uKHR5cGUpIHtcclxuICB2YXIga2V5LCBsaXN0ZW5lcnM7XHJcblxyXG4gIGlmICghdGhpcy5fZXZlbnRzKVxyXG4gICAgcmV0dXJuIHRoaXM7XHJcblxyXG4gIC8vIG5vdCBsaXN0ZW5pbmcgZm9yIHJlbW92ZUxpc3RlbmVyLCBubyBuZWVkIHRvIGVtaXRcclxuICBpZiAoIXRoaXMuX2V2ZW50cy5yZW1vdmVMaXN0ZW5lcikge1xyXG4gICAgaWYgKGFyZ3VtZW50cy5sZW5ndGggPT09IDApXHJcbiAgICAgIHRoaXMuX2V2ZW50cyA9IHt9O1xyXG4gICAgZWxzZSBpZiAodGhpcy5fZXZlbnRzW3R5cGVdKVxyXG4gICAgICBkZWxldGUgdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG4gICAgcmV0dXJuIHRoaXM7XHJcbiAgfVxyXG5cclxuICAvLyBlbWl0IHJlbW92ZUxpc3RlbmVyIGZvciBhbGwgbGlzdGVuZXJzIG9uIGFsbCBldmVudHNcclxuICBpZiAoYXJndW1lbnRzLmxlbmd0aCA9PT0gMCkge1xyXG4gICAgZm9yIChrZXkgaW4gdGhpcy5fZXZlbnRzKSB7XHJcbiAgICAgIGlmIChrZXkgPT09ICdyZW1vdmVMaXN0ZW5lcicpIGNvbnRpbnVlO1xyXG4gICAgICB0aGlzLnJlbW92ZUFsbExpc3RlbmVycyhrZXkpO1xyXG4gICAgfVxyXG4gICAgdGhpcy5yZW1vdmVBbGxMaXN0ZW5lcnMoJ3JlbW92ZUxpc3RlbmVyJyk7XHJcbiAgICB0aGlzLl9ldmVudHMgPSB7fTtcclxuICAgIHJldHVybiB0aGlzO1xyXG4gIH1cclxuXHJcbiAgbGlzdGVuZXJzID0gdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG5cclxuICBpZiAoaXNGdW5jdGlvbihsaXN0ZW5lcnMpKSB7XHJcbiAgICB0aGlzLnJlbW92ZUxpc3RlbmVyKHR5cGUsIGxpc3RlbmVycyk7XHJcbiAgfSBlbHNlIHtcclxuICAgIC8vIExJRk8gb3JkZXJcclxuICAgIHdoaWxlIChsaXN0ZW5lcnMubGVuZ3RoKVxyXG4gICAgICB0aGlzLnJlbW92ZUxpc3RlbmVyKHR5cGUsIGxpc3RlbmVyc1tsaXN0ZW5lcnMubGVuZ3RoIC0gMV0pO1xyXG4gIH1cclxuICBkZWxldGUgdGhpcy5fZXZlbnRzW3R5cGVdO1xyXG5cclxuICByZXR1cm4gdGhpcztcclxufTtcclxuXHJcbkV2ZW50RW1pdHRlci5wcm90b3R5cGUubGlzdGVuZXJzID0gZnVuY3Rpb24odHlwZSkge1xyXG4gIHZhciByZXQ7XHJcbiAgaWYgKCF0aGlzLl9ldmVudHMgfHwgIXRoaXMuX2V2ZW50c1t0eXBlXSlcclxuICAgIHJldCA9IFtdO1xyXG4gIGVsc2UgaWYgKGlzRnVuY3Rpb24odGhpcy5fZXZlbnRzW3R5cGVdKSlcclxuICAgIHJldCA9IFt0aGlzLl9ldmVudHNbdHlwZV1dO1xyXG4gIGVsc2VcclxuICAgIHJldCA9IHRoaXMuX2V2ZW50c1t0eXBlXS5zbGljZSgpO1xyXG4gIHJldHVybiByZXQ7XHJcbn07XHJcblxyXG5FdmVudEVtaXR0ZXIubGlzdGVuZXJDb3VudCA9IGZ1bmN0aW9uKGVtaXR0ZXIsIHR5cGUpIHtcclxuICB2YXIgcmV0O1xyXG4gIGlmICghZW1pdHRlci5fZXZlbnRzIHx8ICFlbWl0dGVyLl9ldmVudHNbdHlwZV0pXHJcbiAgICByZXQgPSAwO1xyXG4gIGVsc2UgaWYgKGlzRnVuY3Rpb24oZW1pdHRlci5fZXZlbnRzW3R5cGVdKSlcclxuICAgIHJldCA9IDE7XHJcbiAgZWxzZVxyXG4gICAgcmV0ID0gZW1pdHRlci5fZXZlbnRzW3R5cGVdLmxlbmd0aDtcclxuICByZXR1cm4gcmV0O1xyXG59O1xyXG5cclxuZnVuY3Rpb24gaXNGdW5jdGlvbihhcmcpIHtcclxuICByZXR1cm4gdHlwZW9mIGFyZyA9PT0gJ2Z1bmN0aW9uJztcclxufVxyXG5cclxuZnVuY3Rpb24gaXNOdW1iZXIoYXJnKSB7XHJcbiAgcmV0dXJuIHR5cGVvZiBhcmcgPT09ICdudW1iZXInO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpc09iamVjdChhcmcpIHtcclxuICByZXR1cm4gdHlwZW9mIGFyZyA9PT0gJ29iamVjdCcgJiYgYXJnICE9PSBudWxsO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpc1VuZGVmaW5lZChhcmcpIHtcclxuICByZXR1cm4gYXJnID09PSB2b2lkIDA7XHJcbn1cclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcblxyXG4vKioqKioqKioqKiogIFrDgVZJU0xPU1RJICAqKioqKioqKioqKi9cclxudmFyIFByb2ZpbGVQaG90byA9IHJlcXVpcmUoJy4uL2NvbXBvbmVudHMvcHJvZmlsZScpLlByb2ZpbGVQaG90bztcclxudmFyIGFjdGlvbnMgPSByZXF1aXJlKCcuLi9mbHV4L2FjdGlvbnMvY2hhdC9FeGFtcGxlQWN0aW9uQ3JlYXRvcnMnKTtcclxudmFyIG1lc3NhZ2VTdG9yZSA9IHJlcXVpcmUoJy4uL2ZsdXgvc3RvcmVzL2NoYXQvTWVzc2FnZVN0b3JlJyk7XHJcblxyXG4vKioqKioqKioqKiogIE5BU1RBVkVOw40gICoqKioqKioqKioqL1xyXG5cclxuLyoqIE9ka2F6eSBrZSBrb211bmlrYWNpICovXHJcbnZhciByZWFjdFNlbmRNZXNzYWdlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlYWN0Q2hhdFNlbmRNZXNzYWdlTGluaycpO1xyXG52YXIgcmVhY3RSZWZyZXNoTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0UmVmcmVzaE1lc3NhZ2VzTGluaycpO1xyXG52YXIgcmVhY3RMb2FkTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0TG9hZE1lc3NhZ2VzTGluaycpO1xyXG52YXIgcmVhY3RHZXRPbGRlck1lc3NhZ2VzID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlYWN0Q2hhdEdldE9sZGVyTWVzc2FnZXNMaW5rJyk7XHJcbi8qIGsgcG9zbMOhbsOtIHpwcsOhdnkqL1xyXG52YXIgcmVhY3RTZW5kTWVzc2FnZUxpbmsgPSByZWFjdFNlbmRNZXNzYWdlLmhyZWY7XHJcbi8qIGsgcHJhdmlkZWxuw6ltdSBkb3RhenUgbmEgenByw6F2eSAqL1xyXG52YXIgcmVhY3RSZWZyZXNoTWVzc2FnZXNMaW5rID0gcmVhY3RSZWZyZXNoTWVzc2FnZXMuaHJlZjtcclxuLyogayBkb3RhenUgbmEgbmHEjXRlbsOtIHpwcsOhdiwga2R5xb4gbmVtw6FtIHphdMOtbSDFvsOhZG7DqSAodHlwaWNreSBwb3NsZWRuw60genByw6F2eSBtZXppIHXFvml2YXRlbGkpICovXHJcbnZhciByZWFjdExvYWRNZXNzYWdlc0xpbmsgPSByZWFjdExvYWRNZXNzYWdlcy5ocmVmO1xyXG4vKiBrIGRvdGF6dSBuYSBzdGFyxaHDrSB6cHLDoXZ5ICovXHJcbnZhciByZWFjdEdldE9sZGVyTWVzc2FnZXNMaW5rID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzLmhyZWY7XHJcbi8qKiBwcmVmaXggcMWZZWQgcGFyYW1ldHJ5IGRvIHVybCAqL1xyXG52YXIgcGFyYW1ldGVyc1ByZWZpeCA9IHJlYWN0U2VuZE1lc3NhZ2UuZGF0YXNldC5wYXJwcmVmaXg7XHJcbi8qKiBvYnZ5a2zDvSBwb8SNZXQgcMWZw61jaG96w61jaCB6cHLDoXYgdiBvZHBvdsSbZGkgdSBwcmF2aWRlbG7DqWhvIGEgaW5pY2nDoWxuw61obyBwb8W+YWRhdmt1IChhbmViIGtvbGlrIHpwcsOhdiBtaSBwxZlpamRlLCBrZHnFviBqaWNoIGplIG5hIHNlcnZlcnUgamXFoXTEmyBkb3N0KSAqL1xyXG52YXIgdXN1YWxHZXRPbGRlck1lc3NhZ2VzQ291bnQgPSByZWFjdEdldE9sZGVyTWVzc2FnZXMuZGF0YXNldC5tYXhtZXNzYWdlcztcclxudmFyIHVzdWFsTG9hZE1lc3NhZ2VzQ291bnQgPSByZWFjdExvYWRNZXNzYWdlcy5kYXRhc2V0Lm1heG1lc3NhZ2VzO1xyXG5cclxuLyoqKioqKioqKioqICBERUZJTklDRSAgKioqKioqKioqKiovXHJcbi8qKiDEjMOhc3Qgb2tuYSwga3RlcsOhIG3DoSBzdmlzbMO9IHBvc3V2bsOtayAtIG9ic2FodWplIHpwcsOhdnksIHRsYcSNw610a28gcHJvIGRvbmHEjcOtdMOhbsOtLi4uICovXHJcbnZhciBNZXNzYWdlc1dpbmRvdyA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlc1dpbmRvd1wiLFxyXG4gIGdldEluaXRpYWxTdGF0ZTogZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4ge21lc3NhZ2VzOiBbXSwgdGhlcmVJc01vcmU6IHRydWUsIGhyZWY6ICcnIH07XHJcbiAgfSxcclxuICBjb21wb25lbnREaWRNb3VudDogZnVuY3Rpb24oKSB7XHJcbiAgICBnZXRJbml0aWFsTWVzc2FnZXModGhpcywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgcHJlcGVuZERhdGFJbnRvQ29tcG9uZW50KTtcclxuICAgIGFjdGlvbnMuY3JlYXRlVGVzdCgpO1xyXG4gIH0sXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBtZXNzYWdlcyA9IHRoaXMuc3RhdGUubWVzc2FnZXM7XHJcbiAgICB2YXIgb2xkZXN0SWQgPSB0aGlzLmdldE9sZGVzdElkKG1lc3NhZ2VzKTtcclxuICAgIC8qIHNlc3RhdmVuw60gb2RrYXp1IHBybyB0bGHEjcOtdGtvICovXHJcbiAgICB2YXIgbW9yZUJ1dHRvbkxpbmsgPSByZWFjdEdldE9sZGVyTWVzc2FnZXNMaW5rICsgJyYnICsgcGFyYW1ldGVyc1ByZWZpeCArICdsYXN0SWQ9JyArIG9sZGVzdElkICsgJyYnICsgcGFyYW1ldGVyc1ByZWZpeCArICd3aXRoVXNlcklkPScgKyB0aGlzLnByb3BzLnVzZXJDb2RlZElkO1xyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VzV2luZG93XCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KExvYWRNb3JlQnV0dG9uLCB7bG9hZEhyZWY6IG1vcmVCdXR0b25MaW5rLCBsb2FkVG86IHRoaXMsIG9sZGVzdElkOiBvbGRlc3RJZCwgdGhlcmVJc01vcmU6IHRoaXMuc3RhdGUudGhlcmVJc01vcmUsIHVzZXJDb2RlZElkOiB0aGlzLnByb3BzLnVzZXJDb2RlZElkfSksIFxyXG4gICAgICAgIG1lc3NhZ2VzLm1hcChmdW5jdGlvbihtZXNzYWdlKXtcclxuICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoTWVzc2FnZSwge2tleTogbWVzc2FnZS5pZCwgbWVzc2FnZURhdGE6IG1lc3NhZ2UsIHVzZXJIcmVmOiBtZXNzYWdlLnByb2ZpbGVIcmVmLCBwcm9maWxlUGhvdG9Vcmw6IG1lc3NhZ2UucHJvZmlsZVBob3RvVXJsfSk7XHJcbiAgICAgICAgfSlcclxuICAgICAgICBcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9LFxyXG4gIGdldE9sZGVzdElkOiBmdW5jdGlvbihtZXNzYWdlcyl7XHJcbiAgICByZXR1cm4gKG1lc3NhZ2VzWzBdKSA/IG1lc3NhZ2VzWzBdLmlkIDogOTAwNzE5OTI1NDc0MDk5MTsgLypuYXN0YXZlbsOtIGhvZG5vdHkgbmVibyBtYXhpbcOhbG7DrSBob2Rub3R5LCBrZHnFviBuZW7DrSovXHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBKZWRuYSB6cHLDoXZhLiAqL1xyXG52YXIgTWVzc2FnZSA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBtZXNzYWdlID0gdGhpcy5wcm9wcy5tZXNzYWdlRGF0YTtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlXCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiB0aGlzLnByb3BzLnVzZXJIcmVmLCB1c2VyTmFtZTogbWVzc2FnZS5uYW1lLCBwcm9maWxlUGhvdG9Vcmw6IHRoaXMucHJvcHMucHJvZmlsZVBob3RvVXJsfSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlQXJyb3dcIn0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwicFwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VUZXh0XCJ9LCBcclxuICAgICAgICAgIG1lc3NhZ2UudGV4dCwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VEYXRldGltZVwifSwgbWVzc2FnZS5zZW5kZWREYXRlKVxyXG4gICAgICAgICksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJjbGVhclwifSlcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqIERvbmHEjcOtdGFjw60gdGxhxI3DrXRrbyAqL1xyXG52YXIgTG9hZE1vcmVCdXR0b24gPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTG9hZE1vcmVCdXR0b25cIixcclxuICByZW5kZXI6IGZ1bmN0aW9uKCkge1xyXG4gICAgaWYoIXRoaXMucHJvcHMudGhlcmVJc01vcmUpeyByZXR1cm4gbnVsbDt9XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwic3BhblwiLCB7Y2xhc3NOYW1lOiBcImxvYWRNb3JlQnV0dG9uIGJ0bi1tYWluIGxvYWRpbmdidXR0b24gdWktYnRuXCIsIG9uQ2xpY2s6IHRoaXMuaGFuZGxlQ2xpY2t9LCBcbiAgICAgICAgXCJOYcSNw61zdCBkYWzFocOtIHpwcsOhdnlcIlxuICAgICAgKVxyXG4gICAgKTtcclxuICB9LFxyXG4gIGhhbmRsZUNsaWNrOiBmdW5jdGlvbigpe1xyXG4gICAgZ2V0T2xkZXJNZXNzYWdlcyh0aGlzLnByb3BzLmxvYWRUbywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgdGhpcy5wcm9wcy5vbGRlc3RJZCwgcHJlcGVuZERhdGFJbnRvQ29tcG9uZW50KTtcclxuICB9XHJcbn0pO1xyXG5cclxuLyoqIEZvcm11bMOhxZkgcHJvIG9kZXPDrWzDoW7DrSB6cHLDoXYgKi9cclxudmFyIE5ld01lc3NhZ2VGb3JtID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIk5ld01lc3NhZ2VGb3JtXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIHZhciBsb2dnZWRVc2VyID0gdGhpcy5wcm9wcy5sb2dnZWRVc2VyO1xyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm5ld01lc3NhZ2VcIn0sIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoUHJvZmlsZVBob3RvLCB7cHJvZmlsZUxpbms6IGxvZ2dlZFVzZXIuaHJlZiwgdXNlck5hbWU6IGxvZ2dlZFVzZXIubmFtZSwgcHJvZmlsZVBob3RvVXJsOiBsb2dnZWRVc2VyLnByb2ZpbGVQaG90b1VybH0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImZvcm1cIiwge29uU3VibWl0OiB0aGlzLm9uU3VibWl0fSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiaW5wdXRcIiwge3R5cGU6IFwidGV4dFwiLCBjbGFzc05hbWU6IFwibWVzc2FnZUlucHV0XCJ9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiaW5wdXRcIiwge3R5cGU6IFwic3VibWl0XCIsIGNsYXNzTmFtZTogXCJidG4tbWFpbiBtZWRpdW0gYnV0dG9uXCIsIHZhbHVlOiBcIk9kZXNsYXRcIn0pXHJcbiAgICAgICAgKVxyXG4gICAgICApXHJcbiAgICApO1xyXG4gIH0sXHJcbiAgb25TdWJtaXQ6IGZ1bmN0aW9uKGUpey8qIFZlem1lIHpwcsOhdnUgemUgc3VibWl0dSBhIHBvxaFsZSBqaS4gVGFrw6kgc21hxb5lIHpwcsOhdnUgbmFwc2Fub3UgdiBpbnB1dHUuICovXHJcbiAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICB2YXIgaW5wdXQgPSBlLnRhcmdldC5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKCdtZXNzYWdlSW5wdXQnKVswXTtcclxuICAgIHZhciBtZXNzYWdlID0gaW5wdXQudmFsdWU7XHJcbiAgICBpZihtZXNzYWdlID09IHVuZGVmaW5lZCB8fCBtZXNzYWdlLnRyaW0oKSA9PSAnJykgcmV0dXJuO1xyXG4gICAgaW5wdXQudmFsdWUgPSAnJztcclxuICAgIHNlbmRNZXNzYWdlKHRoaXMsIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIG1lc3NhZ2UsIGFwcGVuZERhdGFJbnRvQ29tcG9uZW50KTtcclxuICB9XHJcbn0pO1xyXG5cclxubW9kdWxlLmV4cG9ydHMgPSB7XHJcbiAgLyoqIE9rbm8gY2Vsw6lobyBjaGF0dSBzIGplZG7DrW0gdcW+aXZhdGVsZW0gKi9cclxuICBDaGF0V2luZG93OiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiQ2hhdFdpbmRvd1wiLFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbiAoKSB7XHJcbiAgICAgIHJldHVybiAoXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcImNoYXRXaW5kb3dcIn0sIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChNZXNzYWdlc1dpbmRvdywge3VzZXJDb2RlZElkOiB0aGlzLnByb3BzLnVzZXJDb2RlZElkfSksIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChOZXdNZXNzYWdlRm9ybSwge2xvZ2dlZFVzZXI6IHRoaXMucHJvcHMubG9nZ2VkVXNlciwgdXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KVxyXG4gICAgICAgIClcclxuICAgICAgKVxyXG4gICAgfVxyXG4gIH0pXHJcbn07XHJcblxyXG5cclxuXHJcbiAgLyoqKioqKioqKioqICBLT01VTklLQUNFIChqUXVlcnkpICoqKioqKioqKioqL1xyXG5cclxuICAvKipcclxuICAgKiBaw61za8OhIHplIHNlcnZlcnUgcG9zbGVkbsOtY2ggbsSba29saWsgcHJvYsSbaGzDvWNoIHpwcsOhdiBzIHXFvml2YXRlbGVtIHMgZGFuw71tIGlkXHJcbiAgICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGEsIGt0ZXLDoSBzaSB2ecW+w6FkYWxhIGRhdGFcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlXHJcbiAgICogQHBhcmFtICB7RnVuY3Rpb259IGNhbGxiYWNrICAgIGZ1bmtjZSwga3RlcsOhIHNlIHphdm9sw6EgcMWZaSBvYmRyxb5lbsOtIG9kcG92xJtkaVxyXG4gICAqL1xyXG4gIHZhciBnZXRJbml0aWFsTWVzc2FnZXMgPSBmdW5jdGlvbihjb21wb25lbnQsIHVzZXJDb2RlZElkLCBjYWxsYmFjayl7XHJcbiAgICB2YXIgZGF0YSA9IHt9O1xyXG4gIFx0ZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ2Zyb21JZCddID0gdXNlckNvZGVkSWQ7XHJcblxyXG4gICAgJC5nZXRKU09OKHJlYWN0TG9hZE1lc3NhZ2VzTGluaywgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHJldHVybjtcclxuICAgICAgICBjYWxsYmFjayhjb21wb25lbnQsIHVzZXJDb2RlZElkLCByZXN1bHQsIHVzdWFsTG9hZE1lc3NhZ2VzQ291bnQpO1xyXG4gICAgfSk7XHJcbiAgfTtcclxuXHJcbiAgLyoqXHJcbiAgICogWsOtc2vDoSB6ZSBzZXJ2ZXJ1IG7Em2tvbGlrIHN0YXLFocOtY2ggenByw6F2XHJcbiAgICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGEsIGt0ZXLDoSBidWRlIGFrdHVhbGl6b3bDoW5hIGRhdHlcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlXHJcbiAgICogQHBhcmFtICB7aW50fSAgIG9sZGVzdElkIGlkIG5lanN0YXLFocOtIHpwcsOhdnkgKG5lam1lbsWhw60gem7DoW3DqSBpZClcclxuICAgKiBAcGFyYW0gIHtGdW5jdGlvbn0gY2FsbGJhY2sgICAgZnVua2NlLCBrdGVyw6Egc2UgemF2b2zDoSBwxZlpIG9iZHLFvmVuw60gb2Rwb3bEm2RpXHJcbiAgICovXHJcbiAgdmFyIGdldE9sZGVyTWVzc2FnZXMgPSBmdW5jdGlvbihjb21wb25lbnQsIHVzZXJDb2RlZElkLCBvbGRlc3RJZCwgY2FsbGJhY2spe1xyXG4gICAgdmFyIGRhdGEgPSB7fTtcclxuICBcdGRhdGFbcGFyYW1ldGVyc1ByZWZpeCArICdsYXN0SWQnXSA9IG9sZGVzdElkO1xyXG4gICAgZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ3dpdGhVc2VySWQnXSA9IHVzZXJDb2RlZElkO1xyXG4gICAgJC5nZXRKU09OKHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmssIGRhdGEsIGZ1bmN0aW9uKHJlc3VsdCl7XHJcbiAgICAgICAgaWYocmVzdWx0Lmxlbmd0aCA9PSAwKSByZXR1cm47XHJcbiAgICAgICAgY2FsbGJhY2soY29tcG9uZW50LCB1c2VyQ29kZWRJZCwgcmVzdWx0LCB1c3VhbEdldE9sZGVyTWVzc2FnZXNDb3VudCk7XHJcbiAgICB9KTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBQb8WhbGUgbmEgc2VydmVyIHpwcsOhdnUuXHJcbiAgICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGEsIGt0ZXLDoSBidWRlIGFrdHVhbGl6b3bDoW5hIGRhdHlcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlXHJcbiAgICogQHBhcmFtICB7U3RyaW5nfSBtZXNzYWdlIHRleHQgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge0Z1bmN0aW9ufSBjYWxsYmFjayAgICBmdW5rY2UsIGt0ZXLDoSBzZSB6YXZvbMOhIHDFmWkgb2JkcsW+ZW7DrSBvZHBvdsSbZGkgKG9kZXNsYW7DoSB6cHLDoXZhIHDFmWlqZGUgenDEm3QpXHJcbiAgICovXHJcbiAgdmFyIHNlbmRNZXNzYWdlID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwgbWVzc2FnZSwgY2FsbGJhY2spe1xyXG4gICAgdmFyIGRhdGEgPSB7XHJcbiAgICAgIHRvOiB1c2VyQ29kZWRJZCxcclxuICAgICAgdHlwZTogJ3RleHRNZXNzYWdlJyxcclxuICAgICAgdGV4dDogbWVzc2FnZVxyXG4gICAgfTtcclxuICAgIHZhciBqc29uID0gSlNPTi5zdHJpbmdpZnkoZGF0YSk7XHJcbiAgXHRcdCQuYWpheCh7XHJcbiAgXHRcdFx0ZGF0YVR5cGU6IFwianNvblwiLFxyXG4gIFx0XHRcdHR5cGU6ICdQT1NUJyxcclxuICBcdFx0XHR1cmw6IHJlYWN0U2VuZE1lc3NhZ2VMaW5rLFxyXG4gIFx0XHRcdGRhdGE6IGpzb24sXHJcbiAgXHRcdFx0Y29udGVudFR5cGU6ICdhcHBsaWNhdGlvbi9qc29uOyBjaGFyc2V0PXV0Zi04JyxcclxuICBcdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgICAgY2FsbGJhY2soY29tcG9uZW50LCB1c2VyQ29kZWRJZCwgcmVzdWx0KTtcclxuICAgICAgICB9XHJcbiAgXHRcdH0pO1xyXG4gIH07XHJcblxyXG4gIC8qKioqKioqKioqKiAgQ0FMTEJBQ0sgRlVOS0NFICAqKioqKioqKioqKi9cclxuXHJcbiAgLyoqXHJcbiAgICogTmFzdGF2w60genByw6F2eSB6ZSBzdGFuZGFyZG7DrWhvIEpTT051IGNoYXR1ICh2aXogZG9rdW1lbnRhY2UpIGRvIHN0YXRlIHDFmWVkYW7DqSBrb21wb25lbnR5IG5hIHphxI3DoXRlayBwxZllZCBvc3RhdG7DrSB6cHLDoXZ5LlxyXG4gICAqIEBwYXJhbSAge1JlYWN0Q2xhc3N9IGNvbXBvbmVudCBrb21wb25lbnRhXHJcbiAgICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICAgKiBAcGFyYW0gIHtqc29ufSBqc29uRGF0YSAgZGF0YSB6ZSBzZXJ2ZXJ1XHJcbiAgICogQHBhcmFtICB7aW50fSB1c3VhbE1lc3NhZ2VzQ291bnQgb2J2eWtsw70gcG/EjWV0IHpwcsOhdiAtIHBva3VkIGplIGRvZHLFvmVuLCB6YWhvZMOtIG5lanN0YXLFocOtIHpwcsOhdnUgKHBva3VkIGplIHpwcsOhdiBkb3N0YXRlaylcclxuICAgKiBhIGtvbXBvbmVudMSbIHBvZGxlIHRvaG8gbmFzdGF2w60gc3Rhdiwgxb5lIG5hIHNlcnZlcnUgamXFoXTEmyBqc291L3XFviBuZWpzb3UgZGFsxaHDrSB6cHLDoXZ5XHJcbiAgICovXHJcbiAgdmFyIHByZXBlbmREYXRhSW50b0NvbXBvbmVudCA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIGpzb25EYXRhLCB1c3VhbE1lc3NhZ2VzQ291bnQpe1xyXG4gICAgdmFyIHRoZXJlSXNNb3JlID0gdHJ1ZTtcclxuICAgIHZhciByZXN1bHQgPSBqc29uRGF0YVt1c2VyQ29kZWRJZF07XHJcbiAgICBpZihyZXN1bHQubWVzc2FnZXMubGVuZ3RoIDwgdXN1YWxNZXNzYWdlc0NvdW50KXsvKiBwb2t1ZCBtw6FtIG3DqW7EmyB6cHLDoXYgbmXFviBqZSBvYnZ5a2zDqSovXHJcbiAgICAgIHRoZXJlSXNNb3JlID0gZmFsc2U7XHJcbiAgICB9ZWxzZXtcclxuICAgICAgcmVzdWx0Lm1lc3NhZ2VzLnNoaWZ0KCk7Lyogb2RlYmVydSBwcnZuw60genByw6F2dSAqL1xyXG4gICAgfVxyXG4gICAgcmVzdWx0LnRoZXJlSXNNb3JlID0gdGhlcmVJc01vcmU7XHJcbiAgICByZXN1bHQubWVzc2FnZXMgPSByZXN1bHQubWVzc2FnZXMuY29uY2F0KGNvbXBvbmVudC5zdGF0ZS5tZXNzYWdlcyk7XHJcbiAgICBjb21wb25lbnQuc2V0U3RhdGUocmVzdWx0KTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBOYXN0YXbDrSB6cHLDoXZ5IHplIHN0YW5kYXJkbsOtaG8gSlNPTnUgY2hhdHUgKHZpeiBkb2t1bWVudGFjZSkgZG8gc3RhdGUgcMWZZWRhbsOpIGtvbXBvbmVudHkgemEgb3N0YXRuw60genByw6F2eS5cclxuICAgKiBAcGFyYW0gIHtSZWFjdENsYXNzfSBjb21wb25lbnQga29tcG9uZW50YVxyXG4gICAqIEBwYXJhbSAge2ludH0gdXNlckNvZGVkSWQgaWQgdcW+aXZhdGVsZSwgb2Qga3RlcsOpaG8gY2hjaSBuYcSNw61zdCB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7anNvbn0ganNvbkRhdGEgIGRhdGEgemUgc2VydmVydVxyXG4gICAqL1xyXG4gIHZhciBhcHBlbmREYXRhSW50b0NvbXBvbmVudCA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIGpzb25EYXRhKXtcclxuICAgIHZhciByZXN1bHQgPSBqc29uRGF0YVt1c2VyQ29kZWRJZF07XHJcbiAgICByZXN1bHQudGhlcmVJc01vcmUgPSB0aGVyZUlzTW9yZTtcclxuICAgIHJlc3VsdC5tZXNzYWdlcyA9IGNvbXBvbmVudC5zdGF0ZS5tZXNzYWdlcy5jb25jYXQocmVzdWx0Lm1lc3NhZ2VzKTtcclxuICAgIGNvbXBvbmVudC5zZXRTdGF0ZShyZXN1bHQpO1xyXG4gIH07XHJcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuXHJcbiAgLyoqIEtvbXBvbmVudGEgbmEgcHJvZmlsb3ZvdSBmb3RrdSAqL1xyXG4gIFByb2ZpbGVQaG90bzogUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIlByb2ZpbGVQaG90b1wiLFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbiAoKSB7XHJcbiAgICAgIHJldHVybiAoXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImFcIiwge2NsYXNzTmFtZTogXCJnZW5lcmF0ZWRQcm9maWxlXCIsIGhyZWY6IHRoaXMucHJvcHMucHJvZmlsZUxpbmssIHRpdGxlOiB0aGlzLnByb3BzLnVzZXJOYW1lfSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiaW1nXCIsIHtzcmM6IHRoaXMucHJvcHMucHJvZmlsZVBob3RvVXJsfSlcclxuICAgICAgICApXHJcbiAgICAgICk7XHJcbiAgICB9XHJcbiAgfSlcclxuXHJcbn07XHJcbiIsIi8qKlxuICogVGhpcyBmaWxlIGlzIHByb3ZpZGVkIGJ5IEZhY2Vib29rIGZvciB0ZXN0aW5nIGFuZCBldmFsdWF0aW9uIHB1cnBvc2VzXG4gKiBvbmx5LiBGYWNlYm9vayByZXNlcnZlcyBhbGwgcmlnaHRzIG5vdCBleHByZXNzbHkgZ3JhbnRlZC5cbiAqXG4gKiBUSEUgU09GVFdBUkUgSVMgUFJPVklERUQgXCJBUyBJU1wiLCBXSVRIT1VUIFdBUlJBTlRZIE9GIEFOWSBLSU5ELCBFWFBSRVNTIE9SXG4gKiBJTVBMSUVELCBJTkNMVURJTkcgQlVUIE5PVCBMSU1JVEVEIFRPIFRIRSBXQVJSQU5USUVTIE9GIE1FUkNIQU5UQUJJTElUWSxcbiAqIEZJVE5FU1MgRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFIEFORCBOT05JTkZSSU5HRU1FTlQuIElOIE5PIEVWRU5UIFNIQUxMXG4gKiBGQUNFQk9PSyBCRSBMSUFCTEUgRk9SIEFOWSBDTEFJTSwgREFNQUdFUyBPUiBPVEhFUiBMSUFCSUxJVFksIFdIRVRIRVIgSU5cbiAqIEFOIEFDVElPTiBPRiBDT05UUkFDVCwgVE9SVCBPUiBPVEhFUldJU0UsIEFSSVNJTkcgRlJPTSwgT1VUIE9GIE9SIElOXG4gKiBDT05ORUNUSU9OIFdJVEggVEhFIFNPRlRXQVJFIE9SIFRIRSBVU0UgT1IgT1RIRVIgREVBTElOR1MgSU4gVEhFIFNPRlRXQVJFLlxuICovXG5cbiB2YXIgZGlzcGF0Y2hlciA9IHJlcXVpcmUoJy4uLy4uL2Rpc3BhdGNoZXIvZGF0ZW5vZGVEaXNwYXRjaGVyJyk7XG4gdmFyIGNvbnN0YW50cyA9IHJlcXVpcmUoJy4uLy4uL2NvbnN0YW50cy9BY3Rpb25Db25zdGFudHMnKTtcbiB2YXIgRXZlbnRFbWl0dGVyID0gcmVxdWlyZSgnZXZlbnRzJykuRXZlbnRFbWl0dGVyO1xuXG52YXIgQWN0aW9uVHlwZXMgPSBjb25zdGFudHMuQWN0aW9uVHlwZXM7XG5cbm1vZHVsZS5leHBvcnRzID0ge1xuXG4gIGNyZWF0ZVRlc3Q6IGZ1bmN0aW9uKCkge1xuICAgIGRpc3BhdGNoZXIuZGlzcGF0Y2goe1xuICAgICAgdHlwZTogQWN0aW9uVHlwZXMuVEVTVFxuICAgIH0pO1xuICB9XG5cbn07XG4iLCIvKlxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxuICovXG5cblxudmFyIGtleU1pcnJvciA9IHJlcXVpcmUoJ2tleW1pcnJvcicpO1xuXG5tb2R1bGUuZXhwb3J0cyA9IHtcblxuICAvKiB0eXB5IGFrY8OtLCBrdGVyw6kgbW9ob3UgbmFzdGF0ICovXG4gIEFjdGlvblR5cGVzOiBrZXlNaXJyb3Ioe1xuICAgIFRFU1QgOiBudWxsXG4gIH0pXG5cbn07XG4iLCIvKlxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxuICovXG5cbnZhciBEaXNwYXRjaGVyID0gcmVxdWlyZSgnZmx1eCcpLkRpc3BhdGNoZXI7XG5cbm1vZHVsZS5leHBvcnRzID0gbmV3IERpc3BhdGNoZXIoKTtcbiIsIi8qKlxuICogVGhpcyBmaWxlIGlzIHByb3ZpZGVkIGJ5IEZhY2Vib29rIGZvciB0ZXN0aW5nIGFuZCBldmFsdWF0aW9uIHB1cnBvc2VzXG4gKiBvbmx5LiBGYWNlYm9vayByZXNlcnZlcyBhbGwgcmlnaHRzIG5vdCBleHByZXNzbHkgZ3JhbnRlZC5cbiAqXG4gKiBUSEUgU09GVFdBUkUgSVMgUFJPVklERUQgXCJBUyBJU1wiLCBXSVRIT1VUIFdBUlJBTlRZIE9GIEFOWSBLSU5ELCBFWFBSRVNTIE9SXG4gKiBJTVBMSUVELCBJTkNMVURJTkcgQlVUIE5PVCBMSU1JVEVEIFRPIFRIRSBXQVJSQU5USUVTIE9GIE1FUkNIQU5UQUJJTElUWSxcbiAqIEZJVE5FU1MgRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFIEFORCBOT05JTkZSSU5HRU1FTlQuIElOIE5PIEVWRU5UIFNIQUxMXG4gKiBGQUNFQk9PSyBCRSBMSUFCTEUgRk9SIEFOWSBDTEFJTSwgREFNQUdFUyBPUiBPVEhFUiBMSUFCSUxJVFksIFdIRVRIRVIgSU5cbiAqIEFOIEFDVElPTiBPRiBDT05UUkFDVCwgVE9SVCBPUiBPVEhFUldJU0UsIEFSSVNJTkcgRlJPTSwgT1VUIE9GIE9SIElOXG4gKiBDT05ORUNUSU9OIFdJVEggVEhFIFNPRlRXQVJFIE9SIFRIRSBVU0UgT1IgT1RIRVIgREVBTElOR1MgSU4gVEhFIFNPRlRXQVJFLlxuICovXG5cbnZhciBEaXNwYXRjaGVyID0gcmVxdWlyZSgnLi4vLi4vZGlzcGF0Y2hlci9kYXRlbm9kZURpc3BhdGNoZXInKTtcbnZhciBjb25zdGFudHMgPSByZXF1aXJlKCcuLi8uLi9jb25zdGFudHMvQWN0aW9uQ29uc3RhbnRzJyk7XG52YXIgRXZlbnRFbWl0dGVyID0gcmVxdWlyZSgnZXZlbnRzJykuRXZlbnRFbWl0dGVyO1xudmFyIGFzc2lnbiA9IHJlcXVpcmUoJ29iamVjdC1hc3NpZ24nKTtcblxudmFyIENIQU5HRV9FVkVOVCA9ICdjaGFuZ2UnO1xuXG52YXIgX21lc3NhZ2VzID0ge307XG5cbnZhciBNZXNzYWdlU3RvcmUgPSBhc3NpZ24oe30sIEV2ZW50RW1pdHRlci5wcm90b3R5cGUsIHtcblxuICBlbWl0Q2hhbmdlOiBmdW5jdGlvbigpIHtcbiAgICB0aGlzLmVtaXQoQ0hBTkdFX0VWRU5UKTtcbiAgfSxcblxuICAvKipcbiAgICogQHBhcmFtIHtmdW5jdGlvbn0gY2FsbGJhY2tcbiAgICovXG4gIGFkZENoYW5nZUxpc3RlbmVyOiBmdW5jdGlvbihjYWxsYmFjaykge1xuICAgIHRoaXMub24oQ0hBTkdFX0VWRU5ULCBjYWxsYmFjayk7XG4gIH0sXG5cbiAgcmVtb3ZlQ2hhbmdlTGlzdGVuZXI6IGZ1bmN0aW9uKGNhbGxiYWNrKSB7XG4gICAgdGhpcy5yZW1vdmVMaXN0ZW5lcihDSEFOR0VfRVZFTlQsIGNhbGxiYWNrKTtcbiAgfSxcblxuICBnZXQ6IGZ1bmN0aW9uKGlkKSB7XG4gICAgcmV0dXJuIF9tZXNzYWdlc1tpZF07XG4gIH0sXG5cbiAgZ2V0QWxsOiBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gX21lc3NhZ2VzO1xuICB9XG5cbn0pO1xuXG5NZXNzYWdlU3RvcmUuZGlzcGF0Y2hUb2tlbiA9IERpc3BhdGNoZXIucmVnaXN0ZXIoZnVuY3Rpb24oYWN0aW9uKSB7XG4gIGNvbnNvbGUubG9nKCdJdCB3b3JrcyEnKTtcbiAgTWVzc2FnZVN0b3JlLmVtaXRDaGFuZ2UoKTtcbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IE1lc3NhZ2VTdG9yZTtcbiIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5cclxuLyoqKioqKioqKioqICBJTklDSUFMSVpBQ0UgICoqKioqKioqKioqL1xyXG52YXIgY2hhdFJvb3QgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0V2luZG93Jyk7XHJcbmlmKHR5cGVvZihjaGF0Um9vdCkgIT0gJ3VuZGVmaW5lZCcgJiYgY2hhdFJvb3QgIT0gbnVsbCl7LypleGlzdHVqZSBlbGVtZW50IHBybyBjaGF0Ki9cclxuICB2YXIgQ2hhdCA9IHJlcXVpcmUoJy4vY2hhdC9yZWFjdENoYXQnKTtcclxuICB2YXIgbG9nZ2VkVXNlciA9IHtcclxuICAgIG5hbWU6IGNoYXRSb290LmRhdGFzZXQudXNlcm5hbWUsXHJcbiAgICBocmVmOiBjaGF0Um9vdC5kYXRhc2V0LnVzZXJocmVmLFxyXG4gICAgcHJvZmlsZVBob3RvVXJsOiBjaGF0Um9vdC5kYXRhc2V0LnByb2ZpbGVwaG90b3VybFxyXG4gIH07XHJcbiAgUmVhY3QucmVuZGVyKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KENoYXQuQ2hhdFdpbmRvdywge3VzZXJDb2RlZElkOiBjaGF0Um9vdC5kYXRhc2V0LnVzZXJpbmNoYXRjb2RlZGlkLCBsb2dnZWRVc2VyOiBsb2dnZWRVc2VyfSksXHJcbiAgICAgIGNoYXRSb290XHJcbiAgKTtcclxufVxyXG4iXX0=
