/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  DEFINICE  ***********/
var rootElement = document.getElementById('reactChatWindow');

var ChatWindow = React.createClass({displayName: "ChatWindow",
  render: function () {
    return (
      React.createElement("div", {className: "chatWindow"}, 
        React.createElement(MessagesWindow, null), 
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
    this.setState(
      {messages: [
        { id: 1, text: "Blably blably bla bla bly bla."},
        { id: 2, text: "Blably blably bla bla bly bla2."}
      ]
      });
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


/***********  RENDER  ***********/

React.render(
  React.createElement(ChatWindow, null),
  rootElement
);

/***********  COMMUNICATION  ***********/
;$(function(){

});
