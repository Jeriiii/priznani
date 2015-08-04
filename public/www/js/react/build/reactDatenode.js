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

},{}],3:[function(require,module,exports){
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

},{"./chat/reactChat":1}]},{},[3])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwic3JjL2NoYXQvcmVhY3RDaGF0LmpzIiwic3JjL2NvbXBvbmVudHMvcHJvZmlsZS5qcyIsInNyYy9yZWFjdERhdGVub2RlLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3RPQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8qXHJcbiAqIEBhdXRob3IgSmFuIEtvdGFsw61rIDxqYW4ua290YWxpay5wcm9AZ21haWwuY29tPlxyXG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAxMy0yMDE1IEt1a3JhbCBDT01QQU5ZIHMuci5vLiAgKlxyXG4gKi9cclxuXHJcbi8qIGdsb2JhbCBSZWFjdCAqLy8qIGFieSBOZXRiZWFucyBuZXZ5aGF6b3ZhbCBjaHlieSBrdsWvbGkgbmVkZWtsYXJvdmFuw6kgcHJvbcSbbm7DqSAqL1xyXG5cclxuICAvKioqKioqKioqKiogIFrDgVZJU0xPU1RJICAqKioqKioqKioqKi9cclxuICB2YXIgUHJvZmlsZVBob3RvID0gcmVxdWlyZSgnLi4vY29tcG9uZW50cy9wcm9maWxlJykuUHJvZmlsZVBob3RvO1xyXG5cclxuICAvKioqKioqKioqKiogIE5BU1RBVkVOw40gICoqKioqKioqKioqL1xyXG5cclxuICAvKiogT2RrYXp5IGtlIGtvbXVuaWthY2kgKi9cclxuICB2YXIgcmVhY3RTZW5kTWVzc2FnZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRTZW5kTWVzc2FnZUxpbmsnKTtcclxuICB2YXIgcmVhY3RSZWZyZXNoTWVzc2FnZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncmVhY3RDaGF0UmVmcmVzaE1lc3NhZ2VzTGluaycpO1xyXG4gIHZhciByZWFjdExvYWRNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRMb2FkTWVzc2FnZXNMaW5rJyk7XHJcbiAgdmFyIHJlYWN0R2V0T2xkZXJNZXNzYWdlcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRHZXRPbGRlck1lc3NhZ2VzTGluaycpO1xyXG4gIC8qIGsgcG9zbMOhbsOtIHpwcsOhdnkqL1xyXG4gIHZhciByZWFjdFNlbmRNZXNzYWdlTGluayA9IHJlYWN0U2VuZE1lc3NhZ2UuaHJlZjtcclxuICAvKiBrIHByYXZpZGVsbsOpbXUgZG90YXp1IG5hIHpwcsOhdnkgKi9cclxuICB2YXIgcmVhY3RSZWZyZXNoTWVzc2FnZXNMaW5rID0gcmVhY3RSZWZyZXNoTWVzc2FnZXMuaHJlZjtcclxuICAvKiBrIGRvdGF6dSBuYSBuYcSNdGVuw60genByw6F2LCBrZHnFviBuZW3DoW0gemF0w61tIMW+w6FkbsOpICh0eXBpY2t5IHBvc2xlZG7DrSB6cHLDoXZ5IG1lemkgdcW+aXZhdGVsaSkgKi9cclxuICB2YXIgcmVhY3RMb2FkTWVzc2FnZXNMaW5rID0gcmVhY3RMb2FkTWVzc2FnZXMuaHJlZjtcclxuICAvKiBrIGRvdGF6dSBuYSBzdGFyxaHDrSB6cHLDoXZ5ICovXHJcbiAgdmFyIHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmsgPSByZWFjdEdldE9sZGVyTWVzc2FnZXMuaHJlZjtcclxuICAvKiogcHJlZml4IHDFmWVkIHBhcmFtZXRyeSBkbyB1cmwgKi9cclxuICB2YXIgcGFyYW1ldGVyc1ByZWZpeCA9IHJlYWN0U2VuZE1lc3NhZ2UuZGF0YXNldC5wYXJwcmVmaXg7XHJcbiAgLyoqIG9idnlrbMO9IHBvxI1ldCBwxZnDrWNob3rDrWNoIHpwcsOhdiB2IG9kcG92xJtkaSB1IHByYXZpZGVsbsOpaG8gYSBpbmljacOhbG7DrWhvIHBvxb5hZGF2a3UgKGFuZWIga29saWsgenByw6F2IG1pIHDFmWlqZGUsIGtkecW+IGppY2ggamUgbmEgc2VydmVydSBqZcWhdMSbIGRvc3QpICovXHJcbiAgdmFyIHVzdWFsR2V0T2xkZXJNZXNzYWdlc0NvdW50ID0gcmVhY3RHZXRPbGRlck1lc3NhZ2VzLmRhdGFzZXQubWF4bWVzc2FnZXM7XHJcbiAgdmFyIHVzdWFsTG9hZE1lc3NhZ2VzQ291bnQgPSByZWFjdExvYWRNZXNzYWdlcy5kYXRhc2V0Lm1heG1lc3NhZ2VzO1xyXG5cclxuICAvKioqKioqKioqKiogIERFRklOSUNFICAqKioqKioqKioqKi9cclxuICAvKiogxIzDoXN0IG9rbmEsIGt0ZXLDoSBtw6Egc3Zpc2zDvSBwb3N1dm7DrWsgLSBvYnNhaHVqZSB6cHLDoXZ5LCB0bGHEjcOtdGtvIHBybyBkb25hxI3DrXTDoW7DrS4uLiAqL1xyXG4gIHZhciBNZXNzYWdlc1dpbmRvdyA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJNZXNzYWdlc1dpbmRvd1wiLFxyXG4gICAgZ2V0SW5pdGlhbFN0YXRlOiBmdW5jdGlvbigpIHtcclxuICAgICAgcmV0dXJuIHttZXNzYWdlczogW10sIHRoZXJlSXNNb3JlOiB0cnVlLCBocmVmOiAnJyB9O1xyXG4gICAgfSxcclxuICAgIGNvbXBvbmVudERpZE1vdW50OiBmdW5jdGlvbigpIHtcclxuICAgICAgZ2V0SW5pdGlhbE1lc3NhZ2VzKHRoaXMsIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIHByZXBlbmREYXRhSW50b0NvbXBvbmVudCk7XHJcbiAgICB9LFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgICAgdmFyIG1lc3NhZ2VzID0gdGhpcy5zdGF0ZS5tZXNzYWdlcztcclxuICAgICAgdmFyIG9sZGVzdElkID0gdGhpcy5nZXRPbGRlc3RJZChtZXNzYWdlcyk7XHJcbiAgICAgIC8qIHNlc3RhdmVuw60gb2RrYXp1IHBybyB0bGHEjcOtdGtvICovXHJcbiAgICAgIHZhciBtb3JlQnV0dG9uTGluayA9IHJlYWN0R2V0T2xkZXJNZXNzYWdlc0xpbmsgKyAnJicgKyBwYXJhbWV0ZXJzUHJlZml4ICsgJ2xhc3RJZD0nICsgb2xkZXN0SWQgKyAnJicgKyBwYXJhbWV0ZXJzUHJlZml4ICsgJ3dpdGhVc2VySWQ9JyArIHRoaXMucHJvcHMudXNlckNvZGVkSWQ7XHJcbiAgICAgIHJldHVybiAoXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VzV2luZG93XCJ9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoTG9hZE1vcmVCdXR0b24sIHtsb2FkSHJlZjogbW9yZUJ1dHRvbkxpbmssIGxvYWRUbzogdGhpcywgb2xkZXN0SWQ6IG9sZGVzdElkLCB0aGVyZUlzTW9yZTogdGhpcy5zdGF0ZS50aGVyZUlzTW9yZSwgdXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgICBtZXNzYWdlcy5tYXAoZnVuY3Rpb24obWVzc2FnZSl7XHJcbiAgICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNyZWF0ZUVsZW1lbnQoTWVzc2FnZSwge2tleTogbWVzc2FnZS5pZCwgbWVzc2FnZURhdGE6IG1lc3NhZ2UsIHVzZXJIcmVmOiBtZXNzYWdlLnByb2ZpbGVIcmVmLCBwcm9maWxlUGhvdG9Vcmw6IG1lc3NhZ2UucHJvZmlsZVBob3RvVXJsfSk7XHJcbiAgICAgICAgICB9KVxyXG4gICAgICAgICAgXHJcbiAgICAgICAgKVxyXG4gICAgICApO1xyXG4gICAgfSxcclxuICAgIGdldE9sZGVzdElkOiBmdW5jdGlvbihtZXNzYWdlcyl7XHJcbiAgICAgIHJldHVybiAobWVzc2FnZXNbMF0pID8gbWVzc2FnZXNbMF0uaWQgOiA5MDA3MTk5MjU0NzQwOTkxOyAvKm5hc3RhdmVuw60gaG9kbm90eSBuZWJvIG1heGltw6FsbsOtIGhvZG5vdHksIGtkecW+IG5lbsOtKi9cclxuICAgIH1cclxuICB9KTtcclxuXHJcbiAgLyoqIEplZG5hIHpwcsOhdmEuICovXHJcbiAgdmFyIE1lc3NhZ2UgPSBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiTWVzc2FnZVwiLFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgICAgdmFyIG1lc3NhZ2UgPSB0aGlzLnByb3BzLm1lc3NhZ2VEYXRhO1xyXG4gICAgICByZXR1cm4gKFxyXG4gICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlXCJ9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoUHJvZmlsZVBob3RvLCB7cHJvZmlsZUxpbms6IHRoaXMucHJvcHMudXNlckhyZWYsIHVzZXJOYW1lOiBtZXNzYWdlLm5hbWUsIHByb2ZpbGVQaG90b1VybDogdGhpcy5wcm9wcy5wcm9maWxlUGhvdG9Vcmx9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwicFwiLCB7Y2xhc3NOYW1lOiBcIm1lc3NhZ2VUZXh0XCJ9LCBcclxuICAgICAgICAgICAgbWVzc2FnZS50ZXh0LCBcclxuICAgICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJtZXNzYWdlRGF0ZXRpbWVcIn0sIG1lc3NhZ2Uuc2VuZGVkRGF0ZSlcclxuICAgICAgICAgICksIFxyXG4gICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImRpdlwiLCB7Y2xhc3NOYW1lOiBcImNsZWFyXCJ9KVxyXG4gICAgICAgIClcclxuICAgICAgKTtcclxuICAgIH1cclxuICB9KTtcclxuXHJcbiAgLyoqIERvbmHEjcOtdGFjw60gdGxhxI3DrXRrbyAqL1xyXG4gIHZhciBMb2FkTW9yZUJ1dHRvbiA9IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJMb2FkTW9yZUJ1dHRvblwiLFxyXG4gICAgcmVuZGVyOiBmdW5jdGlvbigpIHtcclxuICAgICAgaWYoIXRoaXMucHJvcHMudGhlcmVJc01vcmUpeyByZXR1cm4gbnVsbDt9XHJcbiAgICAgIHJldHVybiAoXHJcbiAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcInNwYW5cIiwge2NsYXNzTmFtZTogXCJsb2FkTW9yZUJ1dHRvbiBidG4tbWFpbiBsb2FkaW5nYnV0dG9uIHVpLWJ0blwiLCBvbkNsaWNrOiB0aGlzLmhhbmRsZUNsaWNrfSwgXG4gICAgICAgICAgXCJOYcSNw61zdCBkYWzFocOtIHpwcsOhdnlcIlxuICAgICAgICApXHJcbiAgICAgICk7XHJcbiAgICB9LFxyXG4gICAgaGFuZGxlQ2xpY2s6IGZ1bmN0aW9uKCl7XHJcbiAgICAgIGdldE9sZGVyTWVzc2FnZXModGhpcy5wcm9wcy5sb2FkVG8sIHRoaXMucHJvcHMudXNlckNvZGVkSWQsIHRoaXMucHJvcHMub2xkZXN0SWQsIHByZXBlbmREYXRhSW50b0NvbXBvbmVudCk7XHJcbiAgICB9XHJcbiAgfSk7XHJcblxyXG4gIC8qKiBGb3JtdWzDocWZIHBybyBvZGVzw61sw6Fuw60genByw6F2ICovXHJcbiAgdmFyIE5ld01lc3NhZ2VGb3JtID0gUmVhY3QuY3JlYXRlQ2xhc3Moe2Rpc3BsYXlOYW1lOiBcIk5ld01lc3NhZ2VGb3JtXCIsXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uKCkge1xyXG4gICAgICB2YXIgbG9nZ2VkVXNlciA9IHRoaXMucHJvcHMubG9nZ2VkVXNlcjtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibmV3TWVzc2FnZVwifSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFByb2ZpbGVQaG90bywge3Byb2ZpbGVMaW5rOiBsb2dnZWRVc2VyLmhyZWYsIHVzZXJOYW1lOiBsb2dnZWRVc2VyLm5hbWUsIHByb2ZpbGVQaG90b1VybDogbG9nZ2VkVXNlci5wcm9maWxlUGhvdG9Vcmx9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwibWVzc2FnZUFycm93XCJ9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZm9ybVwiLCB7b25TdWJtaXQ6IHRoaXMub25TdWJtaXR9LCBcclxuICAgICAgICAgICAgUmVhY3QuY3JlYXRlRWxlbWVudChcImlucHV0XCIsIHt0eXBlOiBcInRleHRcIiwgY2xhc3NOYW1lOiBcIm1lc3NhZ2VJbnB1dFwifSksIFxyXG4gICAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiaW5wdXRcIiwge3R5cGU6IFwic3VibWl0XCIsIGNsYXNzTmFtZTogXCJidG4tbWFpbiBtZWRpdW0gYnV0dG9uXCIsIHZhbHVlOiBcIk9kZXNsYXRcIn0pXHJcbiAgICAgICAgICApXHJcbiAgICAgICAgKVxyXG4gICAgICApO1xyXG4gICAgfSxcclxuICAgIG9uU3VibWl0OiBmdW5jdGlvbihlKXsvKiBWZXptZSB6cHLDoXZ1IHplIHN1Ym1pdHUgYSBwb8WhbGUgamkuIFRha8OpIHNtYcW+ZSB6cHLDoXZ1IG5hcHNhbm91IHYgaW5wdXR1LiAqL1xyXG4gICAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICAgIHZhciBpbnB1dCA9IGUudGFyZ2V0LmdldEVsZW1lbnRzQnlDbGFzc05hbWUoJ21lc3NhZ2VJbnB1dCcpWzBdO1xyXG4gICAgICB2YXIgbWVzc2FnZSA9IGlucHV0LnZhbHVlO1xyXG4gICAgICBpZihtZXNzYWdlID09IHVuZGVmaW5lZCB8fCBtZXNzYWdlLnRyaW0oKSA9PSAnJykgcmV0dXJuO1xyXG4gICAgICBpbnB1dC52YWx1ZSA9ICcnO1xyXG4gICAgICBzZW5kTWVzc2FnZSh0aGlzLCB0aGlzLnByb3BzLnVzZXJDb2RlZElkLCBtZXNzYWdlLCBhcHBlbmREYXRhSW50b0NvbXBvbmVudCk7XHJcbiAgICB9XHJcbiAgfSk7XHJcblxyXG5tb2R1bGUuZXhwb3J0cyA9IHtcclxuICAvKiogT2tubyBjZWzDqWhvIGNoYXR1IHMgamVkbsOtbSB1xb5pdmF0ZWxlbSAqL1xyXG4gIENoYXRXaW5kb3c6IFJlYWN0LmNyZWF0ZUNsYXNzKHtkaXNwbGF5TmFtZTogXCJDaGF0V2luZG93XCIsXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiZGl2XCIsIHtjbGFzc05hbWU6IFwiY2hhdFdpbmRvd1wifSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE1lc3NhZ2VzV2luZG93LCB7dXNlckNvZGVkSWQ6IHRoaXMucHJvcHMudXNlckNvZGVkSWR9KSwgXHJcbiAgICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KE5ld01lc3NhZ2VGb3JtLCB7bG9nZ2VkVXNlcjogdGhpcy5wcm9wcy5sb2dnZWRVc2VyLCB1c2VyQ29kZWRJZDogdGhpcy5wcm9wcy51c2VyQ29kZWRJZH0pXHJcbiAgICAgICAgKVxyXG4gICAgICApXHJcbiAgICB9XHJcbiAgfSlcclxufTtcclxuXHJcblxyXG5cclxuICAvKioqKioqKioqKiogIEtPTVVOSUtBQ0UgKGpRdWVyeSkgKioqKioqKioqKiovXHJcblxyXG4gIC8qKlxyXG4gICAqIFrDrXNrw6EgemUgc2VydmVydSBwb3NsZWRuw61jaCBuxJtrb2xpayBwcm9ixJtobMO9Y2ggenByw6F2IHMgdcW+aXZhdGVsZW0gcyBkYW7DvW0gaWRcclxuICAgKiBAcGFyYW0gIHtSZWFjdENsYXNzfSBjb21wb25lbnQga29tcG9uZW50YSwga3RlcsOhIHNpIHZ5xb7DoWRhbGEgZGF0YVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtGdW5jdGlvbn0gY2FsbGJhY2sgICAgZnVua2NlLCBrdGVyw6Egc2UgemF2b2zDoSBwxZlpIG9iZHLFvmVuw60gb2Rwb3bEm2RpXHJcbiAgICovXHJcbiAgdmFyIGdldEluaXRpYWxNZXNzYWdlcyA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIGNhbGxiYWNrKXtcclxuICAgIHZhciBkYXRhID0ge307XHJcbiAgXHRkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnZnJvbUlkJ10gPSB1c2VyQ29kZWRJZDtcclxuXHJcbiAgICAkLmdldEpTT04ocmVhY3RMb2FkTWVzc2FnZXNMaW5rLCBkYXRhLCBmdW5jdGlvbihyZXN1bHQpe1xyXG4gICAgICAgIGlmKHJlc3VsdC5sZW5ndGggPT0gMCkgcmV0dXJuO1xyXG4gICAgICAgIGNhbGxiYWNrKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIHJlc3VsdCwgdXN1YWxMb2FkTWVzc2FnZXNDb3VudCk7XHJcbiAgICB9KTtcclxuICB9O1xyXG5cclxuICAvKipcclxuICAgKiBaw61za8OhIHplIHNlcnZlcnUgbsSba29saWsgc3RhcsWhw61jaCB6cHLDoXZcclxuICAgKiBAcGFyYW0gIHtSZWFjdENsYXNzfSBjb21wb25lbnQga29tcG9uZW50YSwga3RlcsOhIGJ1ZGUgYWt0dWFsaXpvdsOhbmEgZGF0eVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtpbnR9ICAgb2xkZXN0SWQgaWQgbmVqc3RhcsWhw60genByw6F2eSAobmVqbWVuxaHDrSB6bsOhbcOpIGlkKVxyXG4gICAqIEBwYXJhbSAge0Z1bmN0aW9ufSBjYWxsYmFjayAgICBmdW5rY2UsIGt0ZXLDoSBzZSB6YXZvbMOhIHDFmWkgb2JkcsW+ZW7DrSBvZHBvdsSbZGlcclxuICAgKi9cclxuICB2YXIgZ2V0T2xkZXJNZXNzYWdlcyA9IGZ1bmN0aW9uKGNvbXBvbmVudCwgdXNlckNvZGVkSWQsIG9sZGVzdElkLCBjYWxsYmFjayl7XHJcbiAgICB2YXIgZGF0YSA9IHt9O1xyXG4gIFx0ZGF0YVtwYXJhbWV0ZXJzUHJlZml4ICsgJ2xhc3RJZCddID0gb2xkZXN0SWQ7XHJcbiAgICBkYXRhW3BhcmFtZXRlcnNQcmVmaXggKyAnd2l0aFVzZXJJZCddID0gdXNlckNvZGVkSWQ7XHJcbiAgICAkLmdldEpTT04ocmVhY3RHZXRPbGRlck1lc3NhZ2VzTGluaywgZGF0YSwgZnVuY3Rpb24ocmVzdWx0KXtcclxuICAgICAgICBpZihyZXN1bHQubGVuZ3RoID09IDApIHJldHVybjtcclxuICAgICAgICBjYWxsYmFjayhjb21wb25lbnQsIHVzZXJDb2RlZElkLCByZXN1bHQsIHVzdWFsR2V0T2xkZXJNZXNzYWdlc0NvdW50KTtcclxuICAgIH0pO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIFBvxaFsZSBuYSBzZXJ2ZXIgenByw6F2dS5cclxuICAgKiBAcGFyYW0gIHtSZWFjdENsYXNzfSBjb21wb25lbnQga29tcG9uZW50YSwga3RlcsOhIGJ1ZGUgYWt0dWFsaXpvdsOhbmEgZGF0eVxyXG4gICAqIEBwYXJhbSAge2ludH0gICB1c2VyQ29kZWRJZCBrw7Nkb3ZhbsOpIGlkIHXFvml2YXRlbGVcclxuICAgKiBAcGFyYW0gIHtTdHJpbmd9IG1lc3NhZ2UgdGV4dCB6cHLDoXZ5XHJcbiAgICogQHBhcmFtICB7RnVuY3Rpb259IGNhbGxiYWNrICAgIGZ1bmtjZSwga3RlcsOhIHNlIHphdm9sw6EgcMWZaSBvYmRyxb5lbsOtIG9kcG92xJtkaSAob2Rlc2xhbsOhIHpwcsOhdmEgcMWZaWpkZSB6cMSbdClcclxuICAgKi9cclxuICB2YXIgc2VuZE1lc3NhZ2UgPSBmdW5jdGlvbihjb21wb25lbnQsIHVzZXJDb2RlZElkLCBtZXNzYWdlLCBjYWxsYmFjayl7XHJcbiAgICB2YXIgZGF0YSA9IHtcclxuICAgICAgdG86IHVzZXJDb2RlZElkLFxyXG4gICAgICB0eXBlOiAndGV4dE1lc3NhZ2UnLFxyXG4gICAgICB0ZXh0OiBtZXNzYWdlXHJcbiAgICB9O1xyXG4gICAgdmFyIGpzb24gPSBKU09OLnN0cmluZ2lmeShkYXRhKTtcclxuICBcdFx0JC5hamF4KHtcclxuICBcdFx0XHRkYXRhVHlwZTogXCJqc29uXCIsXHJcbiAgXHRcdFx0dHlwZTogJ1BPU1QnLFxyXG4gIFx0XHRcdHVybDogcmVhY3RTZW5kTWVzc2FnZUxpbmssXHJcbiAgXHRcdFx0ZGF0YToganNvbixcclxuICBcdFx0XHRjb250ZW50VHlwZTogJ2FwcGxpY2F0aW9uL2pzb247IGNoYXJzZXQ9dXRmLTgnLFxyXG4gIFx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uKHJlc3VsdCl7XHJcbiAgICAgICAgICBjYWxsYmFjayhjb21wb25lbnQsIHVzZXJDb2RlZElkLCByZXN1bHQpO1xyXG4gICAgICAgIH1cclxuICBcdFx0fSk7XHJcbiAgfTtcclxuXHJcbiAgLyoqKioqKioqKioqICBDQUxMQkFDSyBGVU5LQ0UgICoqKioqKioqKioqL1xyXG5cclxuICAvKipcclxuICAgKiBOYXN0YXbDrSB6cHLDoXZ5IHplIHN0YW5kYXJkbsOtaG8gSlNPTnUgY2hhdHUgKHZpeiBkb2t1bWVudGFjZSkgZG8gc3RhdGUgcMWZZWRhbsOpIGtvbXBvbmVudHkgbmEgemHEjcOhdGVrIHDFmWVkIG9zdGF0bsOtIHpwcsOhdnkuXHJcbiAgICogQHBhcmFtICB7UmVhY3RDbGFzc30gY29tcG9uZW50IGtvbXBvbmVudGFcclxuICAgKiBAcGFyYW0gIHtpbnR9IHVzZXJDb2RlZElkIGlkIHXFvml2YXRlbGUsIG9kIGt0ZXLDqWhvIGNoY2kgbmHEjcOtc3QgenByw6F2eVxyXG4gICAqIEBwYXJhbSAge2pzb259IGpzb25EYXRhICBkYXRhIHplIHNlcnZlcnVcclxuICAgKiBAcGFyYW0gIHtpbnR9IHVzdWFsTWVzc2FnZXNDb3VudCBvYnZ5a2zDvSBwb8SNZXQgenByw6F2IC0gcG9rdWQgamUgZG9kcsW+ZW4sIHphaG9kw60gbmVqc3RhcsWhw60genByw6F2dSAocG9rdWQgamUgenByw6F2IGRvc3RhdGVrKVxyXG4gICAqIGEga29tcG9uZW50xJsgcG9kbGUgdG9obyBuYXN0YXbDrSBzdGF2LCDFvmUgbmEgc2VydmVydSBqZcWhdMSbIGpzb3UvdcW+IG5lanNvdSBkYWzFocOtIHpwcsOhdnlcclxuICAgKi9cclxuICB2YXIgcHJlcGVuZERhdGFJbnRvQ29tcG9uZW50ID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwganNvbkRhdGEsIHVzdWFsTWVzc2FnZXNDb3VudCl7XHJcbiAgICB2YXIgdGhlcmVJc01vcmUgPSB0cnVlO1xyXG4gICAgdmFyIHJlc3VsdCA9IGpzb25EYXRhW3VzZXJDb2RlZElkXTtcclxuICAgIGlmKHJlc3VsdC5tZXNzYWdlcy5sZW5ndGggPCB1c3VhbE1lc3NhZ2VzQ291bnQpey8qIHBva3VkIG3DoW0gbcOpbsSbIHpwcsOhdiBuZcW+IGplIG9idnlrbMOpKi9cclxuICAgICAgdGhlcmVJc01vcmUgPSBmYWxzZTtcclxuICAgIH1lbHNle1xyXG4gICAgICByZXN1bHQubWVzc2FnZXMuc2hpZnQoKTsvKiBvZGViZXJ1IHBydm7DrSB6cHLDoXZ1ICovXHJcbiAgICB9XHJcbiAgICByZXN1bHQudGhlcmVJc01vcmUgPSB0aGVyZUlzTW9yZTtcclxuICAgIHJlc3VsdC5tZXNzYWdlcyA9IHJlc3VsdC5tZXNzYWdlcy5jb25jYXQoY29tcG9uZW50LnN0YXRlLm1lc3NhZ2VzKTtcclxuICAgIGNvbXBvbmVudC5zZXRTdGF0ZShyZXN1bHQpO1xyXG4gIH07XHJcblxyXG4gIC8qKlxyXG4gICAqIE5hc3RhdsOtIHpwcsOhdnkgemUgc3RhbmRhcmRuw61obyBKU09OdSBjaGF0dSAodml6IGRva3VtZW50YWNlKSBkbyBzdGF0ZSBwxZllZGFuw6kga29tcG9uZW50eSB6YSBvc3RhdG7DrSB6cHLDoXZ5LlxyXG4gICAqIEBwYXJhbSAge1JlYWN0Q2xhc3N9IGNvbXBvbmVudCBrb21wb25lbnRhXHJcbiAgICogQHBhcmFtICB7aW50fSB1c2VyQ29kZWRJZCBpZCB1xb5pdmF0ZWxlLCBvZCBrdGVyw6lobyBjaGNpIG5hxI3DrXN0IHpwcsOhdnlcclxuICAgKiBAcGFyYW0gIHtqc29ufSBqc29uRGF0YSAgZGF0YSB6ZSBzZXJ2ZXJ1XHJcbiAgICovXHJcbiAgdmFyIGFwcGVuZERhdGFJbnRvQ29tcG9uZW50ID0gZnVuY3Rpb24oY29tcG9uZW50LCB1c2VyQ29kZWRJZCwganNvbkRhdGEpe1xyXG4gICAgdmFyIHJlc3VsdCA9IGpzb25EYXRhW3VzZXJDb2RlZElkXTtcclxuICAgIHJlc3VsdC50aGVyZUlzTW9yZSA9IHRoZXJlSXNNb3JlO1xyXG4gICAgcmVzdWx0Lm1lc3NhZ2VzID0gY29tcG9uZW50LnN0YXRlLm1lc3NhZ2VzLmNvbmNhdChyZXN1bHQubWVzc2FnZXMpO1xyXG4gICAgY29tcG9uZW50LnNldFN0YXRlKHJlc3VsdCk7XHJcbiAgfTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcbm1vZHVsZS5leHBvcnRzID0ge1xyXG5cclxuICAvKiogS29tcG9uZW50YSBuYSBwcm9maWxvdm91IGZvdGt1ICovXHJcbiAgUHJvZmlsZVBob3RvOiBSZWFjdC5jcmVhdGVDbGFzcyh7ZGlzcGxheU5hbWU6IFwiUHJvZmlsZVBob3RvXCIsXHJcbiAgICByZW5kZXI6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgcmV0dXJuIChcclxuICAgICAgICBSZWFjdC5jcmVhdGVFbGVtZW50KFwiYVwiLCB7Y2xhc3NOYW1lOiBcImdlbmVyYXRlZFByb2ZpbGVcIiwgaHJlZjogdGhpcy5wcm9wcy5wcm9maWxlTGluaywgdGl0bGU6IHRoaXMucHJvcHMudXNlck5hbWV9LCBcclxuICAgICAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiwge3NyYzogdGhpcy5wcm9wcy5wcm9maWxlUGhvdG9Vcmx9KVxyXG4gICAgICAgIClcclxuICAgICAgKTtcclxuICAgIH1cclxuICB9KVxyXG5cclxufTtcclxuIiwiLypcclxuICogQGF1dGhvciBKYW4gS290YWzDrWsgPGphbi5rb3RhbGlrLnByb0BnbWFpbC5jb20+XHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDEzLTIwMTUgS3VrcmFsIENPTVBBTlkgcy5yLm8uICAqXHJcbiAqL1xyXG5cclxuLyogZ2xvYmFsIFJlYWN0ICovLyogYWJ5IE5ldGJlYW5zIG5ldnloYXpvdmFsIGNoeWJ5IGt2xa9saSBuZWRla2xhcm92YW7DqSBwcm9txJtubsOpICovXHJcblxyXG4vKioqKioqKioqKiogIElOSUNJQUxJWkFDRSAgKioqKioqKioqKiovXHJcbnZhciBjaGF0Um9vdCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZWFjdENoYXRXaW5kb3cnKTtcclxuaWYodHlwZW9mKGNoYXRSb290KSAhPSAndW5kZWZpbmVkJyAmJiBjaGF0Um9vdCAhPSBudWxsKXsvKmV4aXN0dWplIGVsZW1lbnQgcHJvIGNoYXQqL1xyXG4gIHZhciBDaGF0ID0gcmVxdWlyZSgnLi9jaGF0L3JlYWN0Q2hhdCcpO1xyXG4gIHZhciBsb2dnZWRVc2VyID0ge1xyXG4gICAgbmFtZTogY2hhdFJvb3QuZGF0YXNldC51c2VybmFtZSxcclxuICAgIGhyZWY6IGNoYXRSb290LmRhdGFzZXQudXNlcmhyZWYsXHJcbiAgICBwcm9maWxlUGhvdG9Vcmw6IGNoYXRSb290LmRhdGFzZXQucHJvZmlsZXBob3RvdXJsXHJcbiAgfTtcclxuICBSZWFjdC5yZW5kZXIoXHJcbiAgICAgIFJlYWN0LmNyZWF0ZUVsZW1lbnQoQ2hhdC5DaGF0V2luZG93LCB7dXNlckNvZGVkSWQ6IGNoYXRSb290LmRhdGFzZXQudXNlcmluY2hhdGNvZGVkaWQsIGxvZ2dlZFVzZXI6IGxvZ2dlZFVzZXJ9KSxcclxuICAgICAgY2hhdFJvb3RcclxuICApO1xyXG59XHJcbiJdfQ==
