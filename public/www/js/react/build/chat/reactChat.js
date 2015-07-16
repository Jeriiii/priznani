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

/***********  INICIALIZACE  ***********/

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
    console.log(this.props.relatedWindow);
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwic3JjL2NoYXQvcmVhY3RDaGF0LmpzIiwic3JjL2NvbXBvbmVudHMvcHJvZmlsZS5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDck9BO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCIvKlxyXG4gKiBAYXV0aG9yIEphbiBLb3RhbMOtayA8amFuLmtvdGFsaWsucHJvQGdtYWlsLmNvbT5cclxuICogQGNvcHlyaWdodCBDb3B5cmlnaHQgKGMpIDIwMTMtMjAxNSBLdWtyYWwgQ09NUEFOWSBzLnIuby4gICpcclxuICovXHJcblxyXG4vKiBnbG9iYWwgUmVhY3QgKi8vKiBhYnkgTmV0YmVhbnMgbmV2eWhhem92YWwgY2h5Ynkga3bFr2xpIG5lZGVrbGFyb3ZhbsOpIHByb23Em25uw6kgKi9cclxuXHJcbi8qKioqKioqKioqKiAgWsOBVklTTE9TVEkgICoqKioqKioqKioqL1xyXG52YXIgUHJvZmlsZVBob3RvID0gcmVxdWlyZSgnLi4vY29tcG9uZW50cy9wcm9maWxlJykuUHJvZmlsZVBob3RvO1xyXG5cclxuXHJcbi8qKioqKioqKioqKiAgTkFTVEFWRU7DjSAgKioqKioqKioqKiovXHJcblxyXG4vKiogT2RrYXp5IGtlIGtvbXVuaWthY2kgKi9cclxudmFyIHJlYWN0U2VuZE1lc3NhZ2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0U2VuZE1lc3NhZ2VMaW5rJyk7XHJcbnZhciByZWFjdFJlZnJlc2hNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRSZWZyZXNoTWVzc2FnZXNMaW5rJyk7XHJcbnZhciByZWFjdExvYWRNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRMb2FkTWVzc2FnZXNMaW5rJyk7XHJcbnZhciByZWFjdEdldE9sZGVyTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0R2V0T2xkZXJNZXNzYWdlc0xpbmsnKTtcclxuLyogayBwb3Nsw6Fuw60genByw6F2eSovXHJcbnZhciByZWFjdFNlbmRNZXNzYWdlTGluayA9IHJlYWN0U2VuZE1lc3NhZ2UuaHJlZjtcclxuLyogayBwcmF2aWRlbG7DqW11IGRvdGF6dSBuYSB6cHLDoXZ5ICovXHJcbnZhciByZWFjdFJlZnJlc2hNZXNzYWdlc0xpbmsgPSByZWFjdFJlZnJlc2hNZXNzYWdlcy5ocmVmO1xyXG4vKiBrIGRvdGF6dSBuYSBuYcSNdGVuw60genByw6F2LCBrZHnFviBuZW3DoW0gemF0w61tIMW+w6FkbsOpICh0eXBpY2t5IHBvc2xlZG7DrSB6cHLDoXZ5IG1lemkgdcW+aXZhdGVsaSkgKi9cclxudmFyIHJlYWN0TG9hZE1lc3NhZ2VzTGluayA9IHJlYWN0TG9hZE1lc3NhZ2VzLmhyZWY7XHJcbi8qIGsgZG90YXp1IG5hIHN0YXLFocOtIHpwcsOhdnkgKi9cclxudmFyIHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmsgPSByZWFjdEdldE9sZGVyTWVzc2FnZXMuaHJlZjtcclxuLyoqIHByZWZpeCBwxZllZCBwYXJhbWV0cnkgZG8gdXJsICovXHJcbnZhciBwYXJhbWV0ZXJzUHJlZml4ID0gcmVhY3RTZW5kTWVzc2FnZS5kYXRhc2V0LnBhcnByZWZpeDtcclxuLyoqIG9idnlrbMO9IHBvxI1ldCBwxZnDrWNob3rDrWNoIHpwcsOhdiB2IG9kcG92xJtkaSB1IHByYXZpZGVsbsOpaG8gYSBpbmljacOhbG7DrWhvIHBvxb5hZGF2a3UgKGFuZWIga29saWsgenByw6F2IG1pIHDFmWlqZGUsIGtkecW+IGppY2ggamUgbmEgc2VydmVydSBqZcWhdMSbIGRvc3QpICovXHJcbnZhciB1c3VhbEdldE9sZGVyTWVzc2FnZXNDb3VudCA9IHJlYWN0R2V0T2xkZXJNZXNzYWdlcy5kYXRhc2V0Lm1heG1lc3NhZ2VzO1xyXG52YXIgdXN1YWxMb2FkTWVzc2FnZXNDb3VudCA9IHJlYWN0TG9hZE1lc3NhZ2VzLmRhdGFzZXQubWF4bWVzc2FnZXM7XHJcblxyXG4vKioqKioqKioqKiogIElOSUNJQUxJWkFDRSAgKioqKioqKioqKiovXHJcblxyXG4vKioqKioqKioqKiogIERFRklOSUNFICAqKioqKioqKioqKi9cclxuXHJcbi8qKiBPa25vIGNlbMOpaG8gY2hhdHUgcyBqZWRuw61tIHXFvml2YXRlbGVtICovXHJcbnZhciBDaGF0V2luZG93ID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkNoYXRXaW5kb3dcIixcclxuICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJjaGF0V2luZG93XCJ9LCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2VzV2luZG93LCB7dXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChOZXdNZXNzYWdlRm9ybSwge2xvZ2dlZFVzZXI6IHRoaXMucHJvcHMubG9nZ2VkVXNlciwgdXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KVxyXG4gICAgICApXHJcbiAgICApXHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiDEjMOhc3Qgb2tuYSwga3RlcsOhIG3DoSBzdmlzbMO9IHBvc3V2bsOtayAtIG9ic2FodWplIHpwcsOhdnksIHRsYcSNw610a28gcHJvIGRvbmHEjcOtdMOhbsOtLi4uICovXHJcbnZhciBNZXNzYWdlc1dpbmRvdyA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlc1dpbmRvd1wiLFxyXG4gIGdldEluaXRpYWxTdGF0ZTogZnVuY3Rpb24oKSB7XHJcbiAgICByZXR1cm4ge21lc3NhZ2VzOiBbXSwgdGhlcmVJc01vcmU6IHRydWUsIGhyZWY6ICcnIH07XHJcbiAgfSxcclxuICBjb21wb25lbnREaWRNb3VudDogZnVuY3Rpb24oKSB7XHJcbiAgICBnZXRJbml0aWFsTWVzc2FnZXModGhpcywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgcHJlcGVuZERhdGFJbnRvQ29tcG9uZW50KTtcclxuICB9LFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbWVzc2FnZXMgPSB0aGlzLnN0YXRlLm1lc3NhZ2VzO1xyXG4gICAgdmFyIG9sZGVzdElkID0gdGhpcy5nZXRPbGRlc3RJZChtZXNzYWdlcyk7XHJcbiAgICAvKiBzZXN0YXZlbsOtIG9ka2F6dSBwcm8gdGxhxI3DrXRrbyAqL1xyXG4gICAgdmFyIG1vcmVCdXR0b25MaW5rID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzTGluayArICcmJyArIHBhcmFtZXRlcnNQcmVmaXggKyAnbGFzdElkPScgKyBvbGRlc3RJZCArICcmJyArIHBhcmFtZXRlcnNQcmVmaXggKyAnd2l0aFVzZXJJZD0nICsgdGhpcy5wcm9wcy51c2VyQ29kZWRJZDtcclxuICAgIHJldHVybiAoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlc1dpbmRvd1wifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChMb2FkTW9yZUJ1dHRvbiwge2xvYWRIcmVmOiBtb3JlQnV0dG9uTGluaywgbG9hZFRvOiB0aGlzLCBvbGRlc3RJZDogb2xkZXN0SWQsIHRoZXJlSXNNb3JlOiB0aGlzLnN0YXRlLnRoZXJlSXNNb3JlLCB1c2VyQ29kZWRJZDogdGhpcy5wcm9wcy51c2VyQ29kZWRJZH0pLCBcclxuICAgICAgICBtZXNzYWdlcy5tYXAoZnVuY3Rpb24obWVzc2FnZSl7XHJcbiAgICAgICAgICAgIHJldHVybiBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2UsIHtrZXk6IG1lc3NhZ2UuaWQsIG1lc3NhZ2VEYXRhOiBtZXNzYWdlLCB1c2VySHJlZjogbWVzc2FnZS5wcm9maWxlSHJlZiwgcHJvZmlsZVBob3RvVXJsOiBtZXNzYWdlLnByb2ZpbGVQaG90b1VybH0pO1xyXG4gICAgICAgIH0pXHJcbiAgICAgICAgXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBnZXRPbGRlc3RJZDogZnVuY3Rpb24obWVzc2FnZXMpe1xyXG4gICAgcmV0dXJuIChtZXNzYWdlc1swXSkgPyBtZXNzYWdlc1swXS5pZCA6IDkwMDcxOTkyNTQ3NDA5OTE7IC8qbmFzdGF2ZW7DrSBob2Rub3R5IG5lYm8gbWF4aW3DoWxuw60gaG9kbm90eSwga2R5xb4gbmVuw60qL1xyXG4gIH1cclxufSk7XHJcblxyXG4vKiogSmVkbmEgenByw6F2YS4gKi9cclxudmFyIE1lc3NhZ2UgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTWVzc2FnZVwiLFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbWVzc2FnZSA9IHRoaXMucHJvcHMubWVzc2FnZURhdGE7XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZVwifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChQcm9maWxlUGhvdG8sIHtwcm9maWxlTGluazogdGhpcy5wcm9wcy51c2VySHJlZiwgdXNlck5hbWU6IG1lc3NhZ2UubmFtZSwgcHJvZmlsZVBob3RvVXJsOiB0aGlzLnByb3BzLnByb2ZpbGVQaG90b1VybH0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInBcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlVGV4dFwifSwgXHJcbiAgICAgICAgICBtZXNzYWdlLnRleHQsIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlRGF0ZXRpbWVcIn0sIG1lc3NhZ2Uuc2VuZGVkRGF0ZSlcclxuICAgICAgICApLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2xlYXJcIn0pXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBEb25hxI3DrXRhY8OtIHRsYcSNw610a28gKi9cclxudmFyIExvYWRNb3JlQnV0dG9uID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIkxvYWRNb3JlQnV0dG9uXCIsXHJcbiAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgIGlmKCF0aGlzLnByb3BzLnRoZXJlSXNNb3JlKXsgcmV0dXJuIG51bGw7fVxyXG4gICAgcmV0dXJuIChcclxuICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJsb2FkTW9yZUJ1dHRvbiBidG4tbWFpbiBsb2FkaW5nYnV0dG9uIHVpLWJ0blwiLCBvbkNsaWNrOiB0aGlzLmhhbmRsZUNsaWNrfSwgXG4gICAgICAgIFwiTmHEjcOtc3QgZGFsxaHDrSB6cHLDoXZ5XCJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBoYW5kbGVDbGljazogZnVuY3Rpb24oKXtcclxuICAgIGdldE9sZGVyTWVzc2FnZXModGhpcy5wcm9wcy5sb2FkVG8sIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIHRoaXMucHJvcHMub2xkZXN0SWQsIHByZXBlbmREYXRhSW50b0NvbXBvbmVudCk7XHJcbiAgfVxyXG59KTtcclxuXHJcbi8qKiBGb3JtdWzDocWZIHBybyBvZGVzw61sw6Fuw60genByw6F2ICovXHJcbnZhciBOZXdNZXNzYWdlRm9ybSA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJOZXdNZXNzYWdlRm9ybVwiLFxyXG4gIHJlbmRlcjogZnVuY3Rpb24oKSB7XHJcbiAgICB2YXIgbG9nZ2VkVXNlciA9IHRoaXMucHJvcHMubG9nZ2VkVXNlcjtcclxuICAgIGNvbnNvbGUubG9nKHRoaXMucHJvcHMucmVsYXRlZFdpbmRvdyk7XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibmV3TWVzc2FnZVwifSwgXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChQcm9maWxlUGhvdG8sIHtwcm9maWxlTGluazogbG9nZ2VkVXNlci5ocmVmLCB1c2VyTmFtZTogbG9nZ2VkVXNlci5uYW1lLCBwcm9maWxlUGhvdG9Vcmw6IGxvZ2dlZFVzZXIucHJvZmlsZVBob3RvVXJsfSksIFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlQXJyb3dcIn0pLCBcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZm9ybVwiLCB7b25TdWJtaXQ6IHRoaXMub25TdWJtaXR9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbnB1dFwiLCB7dHlwZTogXCJ0ZXh0XCIsIGNsYXNzTmFtZTogXCJtZXNzYWdlSW5wdXRcIn0pLCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbnB1dFwiLCB7dHlwZTogXCJzdWJtaXRcIiwgY2xhc3NOYW1lOiBcImJ0bi1tYWluIG1lZGl1bSBidXR0b25cIiwgdmFsdWU6IFwiT2Rlc2xhdFwifSlcclxuICAgICAgICApXHJcbiAgICAgIClcclxuICAgICk7XHJcbiAgfSxcclxuICBvblN1Ym1pdDogZnVuY3Rpb24oZSl7LyogVmV6bWUgenByw6F2dSB6ZSBzdWJtaXR1IGEgcG/FoWxlIGppLiBUYWvDqSBzbWHFvmUgenByw6F2dSBuYXBzYW5vdSB2IGlucHV0dS4gKi9cclxuICAgIGUucHJldmVudERlZmF1bHQoKTtcclxuICAgIHZhciBpbnB1dCA9IGUudGFyZ2V0LmdldEVsZW1lbnRzQnlDbGFzc05hbWUoJ21lc3NhZ2VJbnB1dCcpWzBdO1xyXG4gICAgdmFyIG1lc3NhZ2UgPSBpbnB1dC52YWx1ZTtcclxuICAgIGlmKG1lc3NhZ2UgPT0gdW5kZWZpbmVkIHx8IG1lc3NhZ2UudHJpbSgpID09ICcnKSByZXR1cm47XHJcbiAgICBpbnB1dC52YWx1ZSA9ICcnO1xyXG4gICAgc2VuZE1lc3NhZ2UodGhpcywgdGhpcy5wcm9wcy51c2VyQ29kZWRJZCwgbWVzc2FnZSwgYXBwZW5kRGF0YUludG9Db21wb25lbnQpO1xyXG4gIH1cclxufSk7XHJcblxyXG5cclxuLyoqKioqKioqKioqICBLT01VTklLQUNFIChqUXVlcnkpICoqKioqKioqKioqL1xyXG5cclxuLyoqXHJcbiAqIFrDrXNrw6EgemUgc2VydmVydSBwb3NsZWRuw61jaCBuxJtrb2xpayBwcm9ixJtobMO9Y2ggenByw6F2IHMgdcW+aXZhdGVsZW0gcyBkYW7DvW0gaWRcclxuICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGEsIGt0ZXLDoSBzaSB2ecW+w6FkYWxhIGRhdGFcclxuICogQHBhcmFtICB7aW50fSAgIHVzZXJDb2RlZElkIGvDs2RvdmFuw6kgaWQgdcW+aXZhdGVsZVxyXG4gKiBAcGFyYW0gIHtGdW5jdGlvbn0gY2FsbGJhY2sgICAgZnVua2NlLCBrdGVyw6Egc2UgemF2b2zDoSBwxZlpIG9iZHLFvmVuw60gb2Rwb3bEm2RpXHJcbiAqL1xyXG52YXIgZ2V0SW5pdGlhbE1lc3NhZ2VzID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwgY2FsbGJhY2spe1xyXG4gIHZhciBkYXRhID0ge307XHJcblx0ZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ2Zyb21JZCddID0gdXNlckNvZGVkSWQ7XHJcbiAgJC5nZXRKU09OKHJlYWN0Q2hhdExvYWRNZXNzYWdlc0xpbmssIGRhdGEsIGZ1bmN0aW9uKHJlc3VsdCl7XHJcbiAgICAgIGNhbGxiYWNrKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIHJlc3VsdCwgdXN1YWxMb2FkTWVzc2FnZXNDb3VudCk7XHJcbiAgfSk7XHJcbn07XHJcblxyXG4vKipcclxuICogWsOtc2vDoSB6ZSBzZXJ2ZXJ1IG7Em2tvbGlrIHN0YXLFocOtY2ggenByw6F2XHJcbiAqIEBwYXJhbSAge1JlYWN0Q2xhc3N9IGNvbXBvbmVudCBrb21wb25lbnRhLCBrdGVyw6EgYnVkZSBha3R1YWxpem92w6FuYSBkYXR5XHJcbiAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICogQHBhcmFtICB7aW50fSAgIG9sZGVzdElkIGlkIG5lanN0YXLFocOtIHpwcsOhdnkgKG5lam1lbsWhw60gem7DoW3DqSBpZClcclxuICogQHBhcmFtICB7RnVuY3Rpb259IGNhbGxiYWNrICAgIGZ1bmtjZSwga3RlcsOhIHNlIHphdm9sw6EgcMWZaSBvYmRyxb5lbsOtIG9kcG92xJtkaVxyXG4gKi9cclxudmFyIGdldE9sZGVyTWVzc2FnZXMgPSBmdW5jdGlvbihjb21wb25lbnQsIHVzZXJDb2RlZElkLCBvbGRlc3RJZCwgY2FsbGJhY2spe1xyXG4gIHZhciBkYXRhID0ge307XHJcblx0ZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ2xhc3RJZCddID0gb2xkZXN0SWQ7XHJcbiAgZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ3dpdGhVc2VySWQnXSA9IHVzZXJDb2RlZElkO1xyXG4gICQuZ2V0SlNPTihyZWFjdEdldE9sZGVyTWVzc2FnZXNMaW5rLCBkYXRhLCBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICBjYWxsYmFjayhjb21wb25lbnQsIHVzZXJDb2RlZElkLCByZXN1bHQsIHVzdWFsR2V0T2xkZXJNZXNzYWdlc0NvdW50KTtcclxuICB9KTtcclxufTtcclxuXHJcbi8qKlxyXG4gKiBQb8WhbGUgbmEgc2VydmVyIHpwcsOhdnUuXHJcbiAqIEBwYXJhbSAge1JlYWN0Q2xhc3N9IGNvbXBvbmVudCBrb21wb25lbnRhLCBrdGVyw6EgYnVkZSBha3R1YWxpem92w6FuYSBkYXR5XHJcbiAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICogQHBhcmFtICB7U3RyaW5nfSBtZXNzYWdlIHRleHQgenByw6F2eVxyXG4gKiBAcGFyYW0gIHtGdW5jdGlvbn0gY2FsbGJhY2sgICAgZnVua2NlLCBrdGVyw6Egc2UgemF2b2zDoSBwxZlpIG9iZHLFvmVuw60gb2Rwb3bEm2RpIChvZGVzbGFuw6EgenByw6F2YSBwxZlpamRlIHpwxJt0KVxyXG4gKi9cclxudmFyIHNlbmRNZXNzYWdlID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwgbWVzc2FnZSwgY2FsbGJhY2spe1xyXG4gIHZhciBkYXRhID0ge1xyXG4gICAgdG86IHVzZXJDb2RlZElkLFxyXG4gICAgdHlwZTogJ3RleHRNZXNzYWdlJyxcclxuICAgIHRleHQ6IG1lc3NhZ2VcclxuICB9O1xyXG4gIHZhciBqc29uID0gSlNPTi5zdHJpbmdpZnkoZGF0YSk7XHJcblx0XHQkLmFqYXgoe1xyXG5cdFx0XHRkYXRhVHlwZTogXCJqc29uXCIsXHJcblx0XHRcdHR5cGU6ICdQT1NUJyxcclxuXHRcdFx0dXJsOiByZWFjdFNlbmRNZXNzYWdlTGluayxcclxuXHRcdFx0ZGF0YToganNvbixcclxuXHRcdFx0Y29udGVudFR5cGU6ICdhcHBsaWNhdGlvbi9qc29uOyBjaGFyc2V0PXV0Zi04JyxcclxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBjYWxsYmFjayhjb21wb25lbnQsIHVzZXJDb2RlZElkLCByZXN1bHQpO1xyXG4gICAgICB9XHJcblx0XHR9KTtcclxufTtcclxuXHJcbi8qKioqKioqKioqKiAgQ0FMTEJBQ0sgRlVOS0NFICAqKioqKioqKioqKi9cclxuXHJcbi8qKlxyXG4gKiBOYXN0YXbDrSB6cHLDoXZ5IHplIHN0YW5kYXJkbsOtaG8gSlNPTnUgY2hhdHUgKHZpeiBkb2t1bWVudGFjZSkgZG8gc3RhdGUgcMWZZWRhbsOpIGtvbXBvbmVudHkgbmEgemHEjcOhdGVrIHDFmWVkIG9zdGF0bsOtIHpwcsOhdnkuXHJcbiAqIEBwYXJhbSAge1JlYWN0Q2xhc3N9IGNvbXBvbmVudCBrb21wb25lbnRhXHJcbiAqIEBwYXJhbSAge2ludH0gdXNlckNvZGVkSWQgaWQgdcW+aXZhdGVsZSwgb2Qga3RlcsOpaG8gY2hjaSBuYcSNw61zdCB6cHLDoXZ5XHJcbiAqIEBwYXJhbSAge2pzb259IGpzb25EYXRhICBkYXRhIHplIHNlcnZlcnVcclxuICogQHBhcmFtICB7aW50fSB1c3VhbE1lc3NhZ2VzQ291bnQgb2J2eWtsw70gcG/EjWV0IHpwcsOhdiAtIHBva3VkIGplIGRvZHLFvmVuLCB6YWhvZMOtIG5lanN0YXLFocOtIHpwcsOhdnUgKHBva3VkIGplIHpwcsOhdiBkb3N0YXRlaylcclxuICogYSBrb21wb25lbnTEmyBwb2RsZSB0b2hvIG5hc3RhdsOtIHN0YXYsIMW+ZSBuYSBzZXJ2ZXJ1IGplxaF0xJsganNvdS91xb4gbmVqc291IGRhbMWhw60genByw6F2eVxyXG4gKi9cclxudmFyIHByZXBlbmREYXRhSW50b0NvbXBvbmVudCA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIGpzb25EYXRhLCB1c3VhbE1lc3NhZ2VzQ291bnQpe1xyXG4gIHZhciB0aGVyZUlzTW9yZSA9IHRydWU7XHJcbiAgdmFyIHJlc3VsdCA9IGpzb25EYXRhW3VzZXJDb2RlZElkXTtcclxuICBpZihyZXN1bHQubWVzc2FnZXMubGVuZ3RoIDwgdXN1YWxNZXNzYWdlc0NvdW50KXsvKiBwb2t1ZCBtw6FtIG3DqW7EmyB6cHLDoXYgbmXFviBqZSBvYnZ5a2zDqSovXHJcbiAgICB0aGVyZUlzTW9yZSA9IGZhbHNlO1xyXG4gIH1lbHNle1xyXG4gICAgcmVzdWx0Lm1lc3NhZ2VzLnNoaWZ0KCk7Lyogb2RlYmVydSBwcnZuw60genByw6F2dSAqL1xyXG4gIH1cclxuICByZXN1bHQudGhlcmVJc01vcmUgPSB0aGVyZUlzTW9yZTtcclxuICByZXN1bHQubWVzc2FnZXMgPSByZXN1bHQubWVzc2FnZXMuY29uY2F0KGNvbXBvbmVudC5zdGF0ZS5tZXNzYWdlcyk7XHJcbiAgY29tcG9uZW50LnNldFN0YXRlKHJlc3VsdCk7XHJcbn07XHJcblxyXG4vKipcclxuICogTmFzdGF2w60genByw6F2eSB6ZSBzdGFuZGFyZG7DrWhvIEpTT051IGNoYXR1ICh2aXogZG9rdW1lbnRhY2UpIGRvIHN0YXRlIHDFmWVkYW7DqSBrb21wb25lbnR5IHphIG9zdGF0bsOtIHpwcsOhdnkuXHJcbiAqIEBwYXJhbSAge1JlYWN0Q2xhc3N9IGNvbXBvbmVudCBrb21wb25lbnRhXHJcbiAqIEBwYXJhbSAge2ludH0gdXNlckNvZGVkSWQgaWQgdcW+aXZhdGVsZSwgb2Qga3RlcsOpaG8gY2hjaSBuYcSNw61zdCB6cHLDoXZ5XHJcbiAqIEBwYXJhbSAge2pzb259IGpzb25EYXRhICBkYXRhIHplIHNlcnZlcnVcclxuICovXHJcbnZhciBhcHBlbmREYXRhSW50b0NvbXBvbmVudCA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIGpzb25EYXRhKXtcclxuICB2YXIgcmVzdWx0ID0ganNvbkRhdGFbdXNlckNvZGVkSWRdO1xyXG4gIHJlc3VsdC50aGVyZUlzTW9yZSA9IHRoZXJlSXNNb3JlO1xyXG4gIHJlc3VsdC5tZXNzYWdlcyA9IGNvbXBvbmVudC5zdGF0ZS5tZXNzYWdlcy5jb25jYXQocmVzdWx0Lm1lc3NhZ2VzKTtcclxuICBjb21wb25lbnQuc2V0U3RhdGUocmVzdWx0KTtcclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG5cclxuICAvKiogS29tcG9uZW50YSBuYSBwcm9maWxvdm91IGZvdGt1ICovXHJcbiAgUHJvZmlsZVBob3RvOiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiUHJvZmlsZVBob3RvXCIsXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiYVwiLCB7Y2xhc3NOYW1lOiBcImdlbmVyYXRlZFByb2ZpbGVcIiwgaHJlZjogdGhpcy5wcm9wcy5wcm9maWxlTGluaywgdGl0bGU6IHRoaXMucHJvcHMudXNlck5hbWV9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiwge3NyYzogdGhpcy5wcm9wcy5wcm9maWxlUGhvdG9Vcmx9KVxyXG4gICAgICAgIClcclxuICAgICAgKTtcclxuICAgIH1cclxuICB9KVxyXG5cclxufTtcclxuIl19
