/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  DEFINICE  ***********/
var rootElement = document.getElementById('reactChatWindow');

var ChatWindow = React.createClass({
  render: function () {
    return (
      <div className="chatWindow">
        <MessagesWindow />
        <NewMessageForm />
      </div>
    )
  }
});

var MessagesWindow = React.createClass({
  render: function() {
    return (
      <div className="messagesWindow">
        <LoadMoreButton />
        <Message messageText="Lorem ipsum dolor sit amet." />
        <Message messageText="Lorem ipsum dolor sit amet." />
        <Message messageText="Lorem ipsum dolor sit amet." />
        <Message messageText="Lorem ipsum dolor sit amet." />
      </div>
    );
  }
});

var Message = React.createClass({
  render: function() {
    return (
      <div className="message">
        {this.props.messageText}
      </div>
    );
  }
});

var LoadMoreButton = React.createClass({
  render: function() {
    return (
      <a className="loadMoreButton">
        Načíst další zprávy
      </a>
    );
  }
});

var NewMessageForm = React.createClass({
  render: function() {
    return (
      <form className="newMessageForm">
        <input type="text" />
        <input type="button" value="Odeslat" />
      </form>
    );
  }
});


/***********  RENDER  ***********/

React.render(
  <ChatWindow />,
  rootElement
);
