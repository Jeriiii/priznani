/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  DEFINICE  ***********/
var rootElement = document.getElementById('reactChatWindow');

var messagesWindow = React.createClass({displayName: "messagesWindow",
  render: function() {
    return (
      React.createElement('div', {className: "messagesWindow"})
    );
  }
});

var message = React.createClass({displayName: "message",
  render: function() {
    return (
      React.createElement("div", {className: "message"})
    );
  }
});


/***********  RENDER  ***********/
React.render(
  React.createElement("messagesWindow", null),
  rootElement
);
