/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  DEFINICE  ***********/

var reactSendMessageLink = document.getElementById('reactChatSendMessageLink').href;
var reactRefreshMessagesLink = document.getElementById('reactChatRefreshMessagesLink').href;
var reactLoadMessagesLink = document.getElementById('reactChatLoadMessagesLink').href;
var parametersPrefix = document.getElementById('reactChatSendMessageLink').dataset.parprefix;

var ChatWindow = React.createClass({displayName: "ChatWindow",
  render: function () {
    return (
      React.createElement("div", {className: "chatWindow"}, 
        React.createElement(MessagesWindow, {userCodedId: this.props.userCodedId}), 
        React.createElement(NewMessageForm, null)
      )
    )
  }
});

var MessagesWindow = React.createClass({displayName: "MessagesWindow",
  getInitialState: function() {
    return {messages: [] };
  },
  componentDidMount: function() {
    getInitialMessages(this, this.props.userCodedId, setDataIntoComponent);
  },
  render: function() {
    var messages = this.state.messages;
    return (
      React.createElement("div", {className: "messagesWindow"}, 
        React.createElement(LoadMoreButton, null), 
        messages.map(function(message){
            return React.createElement(Message, {key: message.id, messageData: message});
        })
        
      )
    );
  }
});

var ProfilePhoto = React.createClass({displayName: "ProfilePhoto",
  render: function () {
    return (
      React.createElement("a", {className: "generatedProfile", href: this.props.profileLink, title: this.props.userName}, 
        React.createElement("img", {src: this.props.profilePhotoUrl})
      )
    );
  }
});

var Message = React.createClass({displayName: "Message",
  render: function() {
    var message = this.props.messageData;
    return (
      React.createElement("div", {className: "message"}, 
        React.createElement(ProfilePhoto, {profileLink: "#", userName: "Leopold", profilePhotoUrl: "http://localhost/priznani/public/www/images/users/man.jpg"}), 
        React.createElement("div", {className: "messageArrow"}), 
        React.createElement("p", {className: "messageText"}, 
          message.text, 
          React.createElement("span", {className: "messageDatetime"}, "14:47 10.07.")
        ), 
        React.createElement("div", {className: "clear"})
      )
    );
  }
});

var LoadMoreButton = React.createClass({displayName: "LoadMoreButton",
  render: function() {
    return (
      React.createElement("a", {className: "loadMoreButton btn-main loadingbutton ui-btn"}, 
        "Načíst další zprávy"
      )
    );
  }
});

var NewMessageForm = React.createClass({displayName: "NewMessageForm",
  render: function() {
    return (
      React.createElement("div", {className: "newMessage"}, 
        React.createElement(ProfilePhoto, {profileLink: "#", userName: "Leopold", profilePhotoUrl: "http://localhost/priznani/public/www/images/users/man.jpg"}), 
        React.createElement("div", {className: "messageArrow"}), 
        React.createElement("form", null, 
          React.createElement("input", {type: "text"}), 
          React.createElement("input", {type: "button", className: "btn-main medium button", value: "Odeslat"})
        )
      )
    );
  }
});


/***********  COMMUNICATION  ***********/

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
      callback(component, userCodedId, result);
  });
};

/**
 * Nastaví zprávy ze standardního JSONu chatu (viz dokumentace) do state předané komponenty.
 * @param  {ReactClass} component komponenta
 * @param  {int} userCodedId id uživatele, od kterého chci načíst zprávy
 * @param  {json} jsonData  data ze serveru
 */
var setDataIntoComponent = function(component, userCodedId, jsonData){
  component.setState(jsonData[userCodedId]);
};
