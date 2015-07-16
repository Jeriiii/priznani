(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  ZÁVISLOSTI  ***********/
var ProfilePhoto = require('../components/profile').ProfilePhoto;

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

/** Okno celého chatu s jedním uživatelem */
var ChatWindow = React.createClass({displayName: "ChatWindow",
  render: function () {
    return (
      React.createElement("div", {className: "chatWindow"}, 
        React.createElement(MessagesWindow, {userCodedId: this.props.userCodedId}), 
        React.createElement(NewMessageForm, {loggedUser: this.props.loggedUser, userCodedId: this.props.userCodedId})
      )
    )
  }
});

/** Část okna, která má svislý posuvník - obsahuje zprávy, tlačítko pro donačítání... */
var MessagesWindow = React.createClass({displayName: "MessagesWindow",
  getInitialState: function() {
    return {messages: [], thereIsMore: true, href: '' };
  },
  componentDidMount: function() {
    getInitialMessages(this, this.props.userCodedId, prependDataIntoComponent);
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
  $.getJSON(reactChatLoadMessagesLink, data, function(result){
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

/***********  INICIALIZACE  ***********/
var root = document.getElementById('reactChatWindow');
var loggedUser = {
  name: root.dataset.username,
  href: root.dataset.userhref,
  profilePhotoUrl: root.dataset.profilephotourl
};
React.render(
    React.createElement(ChatWindow, {userCodedId: root.dataset.userinchatcodedid, loggedUser: loggedUser}),
    root
);

},{"../components/profile":2}],2:[function(require,module,exports){
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

},{}]},{},[1])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwic3JjL2NoYXQvcmVhY3RDaGF0LmpzIiwic3JjL2NvbXBvbmVudHMvcHJvZmlsZS5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzdPQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcblxyXG4vKioqKioqKioqKiogIFrDgVZJU0xPU1RJICAqKioqKioqKioqKi9cclxudmFyIFByb2ZpbGVQaG90byA9IHJlcXVpcmUoJy4uL2NvbXBvbmVudHMvcHJvZmlsZScpLlByb2ZpbGVQaG90bztcclxuXHJcbi8qKioqKioqKioqKiAgTkFTVEFWRU7DjSAgKioqKioqKioqKiovXHJcblxyXG4vKiogT2RrYXp5IGtlIGtvbXVuaWthY2kgKi9cclxudmFyIHJlYWN0U2VuZE1lc3NhZ2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0U2VuZE1lc3NhZ2VMaW5rJyk7XHJcbnZhciByZWFjdFJlZnJlc2hNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRSZWZyZXNoTWVzc2FnZXNMaW5rJyk7XHJcbnZhciByZWFjdExvYWRNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRMb2FkTWVzc2FnZXNMaW5rJyk7XHJcbnZhciByZWFjdEdldE9sZGVyTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0R2V0T2xkZXJNZXNzYWdlc0xpbmsnKTtcclxuLyogayBwb3Nsw6Fuw60genByw6F2eSovXHJcbnZhciByZWFjdFNlbmRNZXNzYWdlTGluayA9IHJlYWN0U2VuZE1lc3NhZ2UuaHJlZjtcclxuLyogayBwcmF2aWRlbG7DqW11IGRvdGF6dSBuYSB6cHLDoXZ5ICovXHJcbnZhciByZWFjdFJlZnJlc2hNZXNzYWdlc0xpbmsgPSByZWFjdFJlZnJlc2hNZXNzYWdlcy5ocmVmO1xyXG4vKiBrIGRvdGF6dSBuYSBuYcSNdGVuw60genByw6F2LCBrZHnFviBuZW3DoW0gemF0w61tIMW+w6FkbsOpICh0eXBpY2t5IHBvc2xlZG7DrSB6cHLDoXZ5IG1lemkgdcW+aXZhdGVsaSkgKi9cclxudmFyIHJlYWN0TG9hZE1lc3NhZ2VzTGluayA9IHJlYWN0TG9hZE1lc3NhZ2VzLmhyZWY7XHJcbi8qIGsgZG90YXp1IG5hIHN0YXLFocOtIHpwcsOhdnkgKi9cclxudmFyIHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmsgPSByZWFjdEdldE9sZGVyTWVzc2FnZXMuaHJlZjtcclxuLyoqIHByZWZpeCBwxZllZCBwYXJhbWV0cnkgZG8gdXJsICovXHJcbnZhciBwYXJhbWV0ZXJzUHJlZml4ID0gcmVhY3RTZW5kTWVzc2FnZS5kYXRhc2V0LnBhcnByZWZpeDtcclxuLyoqIG9idnlrbMO9IHBvxI1ldCBwxZnDrWNob3rDrWNoIHpwcsOhdiB2IG9kcG92xJtkaSB1IHByYXZpZGVsbsOpaG8gYSBpbmljacOhbG7DrWhvIHBvxb5hZGF2a3UgKGFuZWIga29saWsgenByw6F2IG1pIHDFmWlqZGUsIGtkecW+IGppY2ggamUgbmEgc2VydmVydSBqZcWhdMSbIGRvc3QpICovXHJcbnZhciB1c3VhbEdldE9sZGVyTWVzc2FnZXNDb3VudCA9IHJlYWN0R2V0T2xkZXJNZXNzYWdlcy5kYXRhc2V0Lm1heG1lc3NhZ2VzO1xyXG52YXIgdXN1YWxMb2FkTWVzc2FnZXNDb3VudCA9IHJlYWN0TG9hZE1lc3NhZ2VzLmRhdGFzZXQubWF4bWVzc2FnZXM7XHJcblxyXG4vKioqKioqKioqKiogIERFRklOSUNFICAqKioqKioqKioqKi9cclxuXHJcbi8qKiBPa25vIGNlbMOpaG8gY2hhdHUgcyBqZWRuw61tIHXFvml2YXRlbGVtICovXHJcbnZhciBDaGF0V2luZG93ID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkNoYXRXaW5kb3dcIixcclxuICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJjaGF0V2luZG93XCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2VzV2luZG93LCB7dXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChOZXdNZXNzYWdlRm9ybSwge2xvZ2dlZFVzZXI6IHRoaXMucHJvcHMubG9nZ2VkVXNlciwgdXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KVxyXG4gICAgICApXHJcbiAgICApXHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiDEjMOhc3Qgb2tuYSwga3RlcsOhIG3DoSBzdmlzbMO9IHBvc3V2bsOtayAtIG9ic2FodWplIHpwcsOhdnksIHRsYcSNw610a28gcHJvIGRvbmHEjcOtdMOhbsOtLi4uICovXHJcbnZhciBNZXNzYWdlc1dpbmRvdyA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlc1dpbmRvd1wiLFxyXG4gIGdldEluaXRpYWxTdGF0ZTogZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4ge21lc3NhZ2VzOiBbXSwgdGhlcmVJc01vcmU6IHRydWUsIGhyZWY6ICcnIH07XHJcbiAgfSxcclxuICBjb21wb25lbnREaWRNb3VudDogZnVuY3Rpb24oKSB7XHJcbiAgICBnZXRJbml0aWFsTWVzc2FnZXModGhpcywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgcHJlcGVuZERhdGFJbnRvQ29tcG9uZW50KTtcclxuICB9LFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbWVzc2FnZXMgPSB0aGlzLnN0YXRlLm1lc3NhZ2VzO1xyXG4gICAgdmFyIG9sZGVzdElkID0gdGhpcy5nZXRPbGRlc3RJZChtZXNzYWdlcyk7XHJcbiAgICAvKiBzZXN0YXZlbsOtIG9ka2F6dSBwcm8gdGxhxI3DrXRrbyAqL1xyXG4gICAgdmFyIG1vcmVCdXR0b25MaW5rID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzTGluayArICcmJyArIHBhcmFtZXRlcnNQcmVmaXggKyAnbGFzdElkPScgKyBvbGRlc3RJZCArICcmJyArIHBhcmFtZXRlcnNQcmVmaXggKyAnd2l0aFVzZXJJZD0nICsgdGhpcy5wcm9wcy51c2VyQ29kZWRJZDtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlc1dpbmRvd1wifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChMb2FkTW9yZUJ1dHRvbiwge2xvYWRIcmVmOiBtb3JlQnV0dG9uTGluaywgbG9hZFRvOiB0aGlzLCBvbGRlc3RJZDogb2xkZXN0SWQsIHRoZXJlSXNNb3JlOiB0aGlzLnN0YXRlLnRoZXJlSXNNb3JlLCB1c2VyQ29kZWRJZDogdGhpcy5wcm9wcy51c2VyQ29kZWRJZH0pLCBcclxuICAgICAgICBtZXNzYWdlcy5tYXAoZnVuY3Rpb24obWVzc2FnZSl7XHJcbiAgICAgICAgICAgIHJldHVybiBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2UsIHtrZXk6IG1lc3NhZ2UuaWQsIG1lc3NhZ2VEYXRhOiBtZXNzYWdlLCB1c2VySHJlZjogbWVzc2FnZS5wcm9maWxlSHJlZiwgcHJvZmlsZVBob3RvVXJsOiBtZXNzYWdlLnByb2ZpbGVQaG90b1VybH0pO1xyXG4gICAgICAgIH0pXHJcbiAgICAgICAgXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBnZXRPbGRlc3RJZDogZnVuY3Rpb24obWVzc2FnZXMpe1xyXG4gICAgcmV0dXJuIChtZXNzYWdlc1swXSkgPyBtZXNzYWdlc1swXS5pZCA6IDkwMDcxOTkyNTQ3NDA5OTE7IC8qbmFzdGF2ZW7DrSBob2Rub3R5IG5lYm8gbWF4aW3DoWxuw60gaG9kbm90eSwga2R5xb4gbmVuw60qL1xyXG4gIH1cclxufSk7XHJcblxyXG4vKiogSmVkbmEgenByw6F2YS4gKi9cclxudmFyIE1lc3NhZ2UgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTWVzc2FnZVwiLFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbWVzc2FnZSA9IHRoaXMucHJvcHMubWVzc2FnZURhdGE7XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZVwifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChQcm9maWxlUGhvdG8sIHtwcm9maWxlTGluazogdGhpcy5wcm9wcy51c2VySHJlZiwgdXNlck5hbWU6IG1lc3NhZ2UubmFtZSwgcHJvZmlsZVBob3RvVXJsOiB0aGlzLnByb3BzLnByb2ZpbGVQaG90b1VybH0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInBcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlVGV4dFwifSwgXHJcbiAgICAgICAgICBtZXNzYWdlLnRleHQsIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlRGF0ZXRpbWVcIn0sIG1lc3NhZ2Uuc2VuZGVkRGF0ZSlcclxuICAgICAgICApLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2xlYXJcIn0pXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBEb25hxI3DrXRhY8OtIHRsYcSNw610a28gKi9cclxudmFyIExvYWRNb3JlQnV0dG9uID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkxvYWRNb3JlQnV0dG9uXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIGlmKCF0aGlzLnByb3BzLnRoZXJlSXNNb3JlKXsgcmV0dXJuIG51bGw7fVxyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJsb2FkTW9yZUJ1dHRvbiBidG4tbWFpbiBsb2FkaW5nYnV0dG9uIHVpLWJ0blwiLCBvbkNsaWNrOiB0aGlzLmhhbmRsZUNsaWNrfSwgXG4gICAgICAgIFwiTmHEjcOtc3QgZGFsxaHDrSB6cHLDoXZ5XCJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBoYW5kbGVDbGljazogZnVuY3Rpb24oKXtcclxuICAgIGdldE9sZGVyTWVzc2FnZXModGhpcy5wcm9wcy5sb2FkVG8sIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIHRoaXMucHJvcHMub2xkZXN0SWQsIHByZXBlbmREYXRhSW50b0NvbXBvbmVudCk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBGb3JtdWzDocWZIHBybyBvZGVzw61sw6Fuw60genByw6F2ICovXHJcbnZhciBOZXdNZXNzYWdlRm9ybSA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJOZXdNZXNzYWdlRm9ybVwiLFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbG9nZ2VkVXNlciA9IHRoaXMucHJvcHMubG9nZ2VkVXNlcjtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJuZXdNZXNzYWdlXCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiBsb2dnZWRVc2VyLmhyZWYsIHVzZXJOYW1lOiBsb2dnZWRVc2VyLm5hbWUsIHByb2ZpbGVQaG90b1VybDogbG9nZ2VkVXNlci5wcm9maWxlUGhvdG9Vcmx9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VBcnJvd1wifSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJmb3JtXCIsIHtvblN1Ym1pdDogdGhpcy5vblN1Ym1pdH0sIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInRleHRcIiwgY2xhc3NOYW1lOiBcIm1lc3NhZ2VJbnB1dFwifSksIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInN1Ym1pdFwiLCBjbGFzc05hbWU6IFwiYnRuLW1haW4gbWVkaXVtIGJ1dHRvblwiLCB2YWx1ZTogXCJPZGVzbGF0XCJ9KVxyXG4gICAgICAgIClcclxuICAgICAgKVxyXG4gICAgKTtcclxuICB9LFxyXG4gIG9uU3VibWl0OiBmdW5jdGlvbihlKXsvKiBWZXptZSB6cHLDoXZ1IHplIHN1Ym1pdHUgYSBwb8WhbGUgamkuIFRha8OpIHNtYcW+ZSB6cHLDoXZ1IG5hcHNhbm91IHYgaW5wdXR1LiAqL1xyXG4gICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xyXG4gICAgdmFyIGlucHV0ID0gZS50YXJnZXQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnbWVzc2FnZUlucHV0JylbMF07XHJcbiAgICB2YXIgbWVzc2FnZSA9IGlucHV0LnZhbHVlO1xyXG4gICAgaWYobWVzc2FnZSA9PSB1bmRlZmluZWQgfHwgbWVzc2FnZS50cmltKCkgPT0gJycpIHJldHVybjtcclxuICAgIGlucHV0LnZhbHVlID0gJyc7XHJcbiAgICBzZW5kTWVzc2FnZSh0aGlzLCB0aGlzLnByb3BzLnVzZXJDb2RlZElkLCBtZXNzYWdlLCBhcHBlbmREYXRhSW50b0NvbXBvbmVudCk7XHJcbiAgfVxyXG59KTtcclxuXHJcblxyXG4vKioqKioqKioqKiogIEtPTVVOSUtBQ0UgKGpRdWVyeSkgKioqKioqKioqKiovXHJcblxyXG4vKipcclxuICogWsOtc2vDoSB6ZSBzZXJ2ZXJ1IHBvc2xlZG7DrWNoIG7Em2tvbGlrIHByb2LEm2hsw71jaCB6cHLDoXYgcyB1xb5pdmF0ZWxlbSBzIGRhbsO9bSBpZFxyXG4gKiBAcGFyYW0gIHtSZWFjdENsYXNzfSBjb21wb25lbnQga29tcG9uZW50YSwga3RlcsOhIHNpIHZ5xb7DoWRhbGEgZGF0YVxyXG4gKiBAcGFyYW0gIHtpbnR9ICAgdXNlckNvZGVkSWQga8OzZG92YW7DqSBpZCB1xb5pdmF0ZWxlXHJcbiAqIEBwYXJhbSAge0Z1bmN0aW9ufSBjYWxsYmFjayAgICBmdW5rY2UsIGt0ZXLDoSBzZSB6YXZvbMOhIHDFmWkgb2JkcsW+ZW7DrSBvZHBvdsSbZGlcclxuICovXHJcbnZhciBnZXRJbml0aWFsTWVzc2FnZXMgPSBmdW5jdGlvbihjb21wb25lbnQsIHVzZXJDb2RlZElkLCBjYWxsYmFjayl7XHJcbiAgdmFyIGRhdGEgPSB7fTtcclxuXHRkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnZnJvbUlkJ10gPSB1c2VyQ29kZWRJZDtcclxuICAkLmdldEpTT04ocmVhY3RDaGF0TG9hZE1lc3NhZ2VzTGluaywgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgY2FsbGJhY2soY29tcG9uZW50LCB1c2VyQ29kZWRJZCwgcmVzdWx0LCB1c3VhbExvYWRNZXNzYWdlc0NvdW50KTtcclxuICB9KTtcclxufTtcclxuXHJcbi8qKlxyXG4gKiBaw61za8OhIHplIHNlcnZlcnUgbsSba29saWsgc3RhcsWhw61jaCB6cHLDoXZcclxuICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGEsIGt0ZXLDoSBidWRlIGFrdHVhbGl6b3bDoW5hIGRhdHlcclxuICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gKiBAcGFyYW0gIHtpbnR9ICAgb2xkZXN0SWQgaWQgbmVqc3RhcsWhw60genByw6F2eSAobmVqbWVuxaHDrSB6bsOhbcOpIGlkKVxyXG4gKiBAcGFyYW0gIHtGdW5jdGlvbn0gY2FsbGJhY2sgICAgZnVua2NlLCBrdGVyw6Egc2UgemF2b2zDoSBwxZlpIG9iZHLFvmVuw60gb2Rwb3bEm2RpXHJcbiAqL1xyXG52YXIgZ2V0T2xkZXJNZXNzYWdlcyA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIG9sZGVzdElkLCBjYWxsYmFjayl7XHJcbiAgdmFyIGRhdGEgPSB7fTtcclxuXHRkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnbGFzdElkJ10gPSBvbGRlc3RJZDtcclxuICBkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnd2l0aFVzZXJJZCddID0gdXNlckNvZGVkSWQ7XHJcbiAgJC5nZXRKU09OKHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmssIGRhdGEsIGZ1bmN0aW9uKHJlc3VsdCl7XHJcbiAgICAgIGNhbGxiYWNrKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIHJlc3VsdCwgdXN1YWxHZXRPbGRlck1lc3NhZ2VzQ291bnQpO1xyXG4gIH0pO1xyXG59O1xyXG5cclxuLyoqXHJcbiAqIFBvxaFsZSBuYSBzZXJ2ZXIgenByw6F2dS5cclxuICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGEsIGt0ZXLDoSBidWRlIGFrdHVhbGl6b3bDoW5hIGRhdHlcclxuICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gKiBAcGFyYW0gIHtTdHJpbmd9IG1lc3NhZ2UgdGV4dCB6cHLDoXZ5XHJcbiAqIEBwYXJhbSAge0Z1bmN0aW9ufSBjYWxsYmFjayAgICBmdW5rY2UsIGt0ZXLDoSBzZSB6YXZvbMOhIHDFmWkgb2JkcsW+ZW7DrSBvZHBvdsSbZGkgKG9kZXNsYW7DoSB6cHLDoXZhIHDFmWlqZGUgenDEm3QpXHJcbiAqL1xyXG52YXIgc2VuZE1lc3NhZ2UgPSBmdW5jdGlvbihjb21wb25lbnQsIHVzZXJDb2RlZElkLCBtZXNzYWdlLCBjYWxsYmFjayl7XHJcbiAgdmFyIGRhdGEgPSB7XHJcbiAgICB0bzogdXNlckNvZGVkSWQsXHJcbiAgICB0eXBlOiAndGV4dE1lc3NhZ2UnLFxyXG4gICAgdGV4dDogbWVzc2FnZVxyXG4gIH07XHJcbiAgdmFyIGpzb24gPSBKU09OLnN0cmluZ2lmeShkYXRhKTtcclxuXHRcdCQuYWpheCh7XHJcblx0XHRcdGRhdGFUeXBlOiBcImpzb25cIixcclxuXHRcdFx0dHlwZTogJ1BPU1QnLFxyXG5cdFx0XHR1cmw6IHJlYWN0U2VuZE1lc3NhZ2VMaW5rLFxyXG5cdFx0XHRkYXRhOiBqc29uLFxyXG5cdFx0XHRjb250ZW50VHlwZTogJ2FwcGxpY2F0aW9uL2pzb247IGNoYXJzZXQ9dXRmLTgnLFxyXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgIGNhbGxiYWNrKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIHJlc3VsdCk7XHJcbiAgICAgIH1cclxuXHRcdH0pO1xyXG59O1xyXG5cclxuLyoqKioqKioqKioqICBDQUxMQkFDSyBGVU5LQ0UgICoqKioqKioqKioqL1xyXG5cclxuLyoqXHJcbiAqIE5hc3RhdsOtIHpwcsOhdnkgemUgc3RhbmRhcmRuw61obyBKU09OdSBjaGF0dSAodml6IGRva3VtZW50YWNlKSBkbyBzdGF0ZSBwxZllZGFuw6kga29tcG9uZW50eSBuYSB6YcSNw6F0ZWsgcMWZZWQgb3N0YXRuw60genByw6F2eS5cclxuICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGFcclxuICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICogQHBhcmFtICB7anNvbn0ganNvbkRhdGEgIGRhdGEgemUgc2VydmVydVxyXG4gKiBAcGFyYW0gIHtpbnR9IHVzdWFsTWVzc2FnZXNDb3VudCBvYnZ5a2zDvSBwb8SNZXQgenByw6F2IC0gcG9rdWQgamUgZG9kcsW+ZW4sIHphaG9kw60gbmVqc3RhcsWhw60genByw6F2dSAocG9rdWQgamUgenByw6F2IGRvc3RhdGVrKVxyXG4gKiBhIGtvbXBvbmVudMSbIHBvZGxlIHRvaG8gbmFzdGF2w60gc3Rhdiwgxb5lIG5hIHNlcnZlcnUgamXFoXTEmyBqc291L3XFviBuZWpzb3UgZGFsxaHDrSB6cHLDoXZ5XHJcbiAqL1xyXG52YXIgcHJlcGVuZERhdGFJbnRvQ29tcG9uZW50ID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwganNvbkRhdGEsIHVzdWFsTWVzc2FnZXNDb3VudCl7XHJcbiAgdmFyIHRoZXJlSXNNb3JlID0gdHJ1ZTtcclxuICB2YXIgcmVzdWx0ID0ganNvbkRhdGFbdXNlckNvZGVkSWRdO1xyXG4gIGlmKHJlc3VsdC5tZXNzYWdlcy5sZW5ndGggPCB1c3VhbE1lc3NhZ2VzQ291bnQpey8qIHBva3VkIG3DoW0gbcOpbsSbIHpwcsOhdiBuZcW+IGplIG9idnlrbMOpKi9cclxuICAgIHRoZXJlSXNNb3JlID0gZmFsc2U7XHJcbiAgfWVsc2V7XHJcbiAgICByZXN1bHQubWVzc2FnZXMuc2hpZnQoKTsvKiBvZGViZXJ1IHBydm7DrSB6cHLDoXZ1ICovXHJcbiAgfVxyXG4gIHJlc3VsdC50aGVyZUlzTW9yZSA9IHRoZXJlSXNNb3JlO1xyXG4gIHJlc3VsdC5tZXNzYWdlcyA9IHJlc3VsdC5tZXNzYWdlcy5jb25jYXQoY29tcG9uZW50LnN0YXRlLm1lc3NhZ2VzKTtcclxuICBjb21wb25lbnQuc2V0U3RhdGUocmVzdWx0KTtcclxufTtcclxuXHJcbi8qKlxyXG4gKiBOYXN0YXbDrSB6cHLDoXZ5IHplIHN0YW5kYXJkbsOtaG8gSlNPTnUgY2hhdHUgKHZpeiBkb2t1bWVudGFjZSkgZG8gc3RhdGUgcMWZZWRhbsOpIGtvbXBvbmVudHkgemEgb3N0YXRuw60genByw6F2eS5cclxuICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGFcclxuICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICogQHBhcmFtICB7anNvbn0ganNvbkRhdGEgIGRhdGEgemUgc2VydmVydVxyXG4gKi9cclxudmFyIGFwcGVuZERhdGFJbnRvQ29tcG9uZW50ID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwganNvbkRhdGEpe1xyXG4gIHZhciByZXN1bHQgPSBqc29uRGF0YVt1c2VyQ29kZWRJZF07XHJcbiAgcmVzdWx0LnRoZXJlSXNNb3JlID0gdGhlcmVJc01vcmU7XHJcbiAgcmVzdWx0Lm1lc3NhZ2VzID0gY29tcG9uZW50LnN0YXRlLm1lc3NhZ2VzLmNvbmNhdChyZXN1bHQubWVzc2FnZXMpO1xyXG4gIGNvbXBvbmVudC5zZXRTdGF0ZShyZXN1bHQpO1xyXG59O1xyXG5cclxuLyoqKioqKioqKioqICBJTklDSUFMSVpBQ0UgICoqKioqKioqKioqL1xyXG52YXIgcm9vdCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRXaW5kb3cnKTtcclxudmFyIGxvZ2dlZFVzZXIgPSB7XHJcbiAgbmFtZTogcm9vdC5kYXRhc2V0LnVzZXJuYW1lLFxyXG4gIGhyZWY6IHJvb3QuZGF0YXNldC51c2VyaHJlZixcclxuICBwcm9maWxlUGhvdG9Vcmw6IHJvb3QuZGF0YXNldC5wcm9maWxlcGhvdG91cmxcclxufTtcclxuUmVhY3QucmVuZGVyKFxyXG4gICAgUmVhY3QuY3JlYXRlRWxlbWVudChDaGF0V2luZG93LCB7dXNlckNvZGVkSWQ6IHJvb3QuZGF0YXNldC51c2VyaW5jaGF0Y29kZWRpZCwgbG9nZ2VkVXNlcjogbG9nZ2VkVXNlcn0pLFxyXG4gICAgcm9vdFxyXG4pO1xyXG4iLCIvKlxyXG4gKiBAYXV0aG9yIEphbiBLb3RhbMOtayA8amFuLmtvdGFsaWsucHJvQGdtYWlsLmNvbT5cclxuICogQGNvcHlyaWdodCBDb3B5cmlnaHQgKGMpIDIwMTMtMjAxNSBLdWtyYWwgQ09NUEFOWSBzLnIuby4gICpcclxuICovXHJcblxyXG4vKiBnbG9iYWwgUmVhY3QgKi8vKiBhYnkgTmV0YmVhbnMgbmV2eWhhem92YWwgY2h5Ynkga3bFr2xpIG5lZGVrbGFyb3ZhbsOpIHByb23Em25uw6kgKi9cclxubW9kdWxlLmV4cG9ydHMgPSB7XHJcblxyXG4gIC8qKiBLb21wb25lbnRhIG5hIHByb2ZpbG92b3UgZm90a3UgKi9cclxuICBQcm9maWxlUGhvdG86IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJQcm9maWxlUGhvdG9cIixcclxuICAgIHJlbmRlcjogZnVuY3Rpb24gKCkge1xyXG4gICAgICByZXR1cm4gKFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJhXCIsIHtjbGFzc05hbWU6IFwiZ2VuZXJhdGVkUHJvZmlsZVwiLCBocmVmOiB0aGlzLnByb3BzLnByb2ZpbGVMaW5rLCB0aXRsZTogdGhpcy5wcm9wcy51c2VyTmFtZX0sIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImltZ1wiLCB7c3JjOiB0aGlzLnByb3BzLnByb2ZpbGVQaG90b1VybH0pXHJcbiAgICAgICAgKVxyXG4gICAgICApO1xyXG4gICAgfVxyXG4gIH0pXHJcblxyXG59O1xyXG4iXX0=
