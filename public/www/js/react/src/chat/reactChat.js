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


var ProfilePhoto = React.createClass({
  render: function () {
    return (
      <a className="generatedProfile" href={this.props.profileLink} title={this.props.userName}>
        <img src={this.props.profilePhotoUrl} />
      </a>
    );
  }
});

var Message = React.createClass({
  render: function() {
    return (
      <div className="message">
        <ProfilePhoto profileLink="#" userName="Leopold" profilePhotoUrl="http://localhost/priznani/public/www/images/users/man.jpg" />
        <div className="messageArrow" />
        <p className="messageText">
          {this.props.messageText}
          <span className="messageDatetime">14:47 10.07.</span>
        </p>
        <div className="clear" />
      </div>
    );
  }
});

var LoadMoreButton = React.createClass({
  render: function() {
    return (
      <a className="loadMoreButton btn-main loadingbutton ui-btn">
        Načíst další zprávy
      </a>
    );
  }
});

var NewMessageForm = React.createClass({
  render: function() {
    return (
      <div className="newMessage">
        <ProfilePhoto profileLink="#" userName="Leopold" profilePhotoUrl="http://localhost/priznani/public/www/images/users/man.jpg" />
        <div className="messageArrow" />
        <form>
          <input type="text" />
          <input type="button" className="btn-main medium button" value="Odeslat" />
        </form>
      </div>
    );
  }
});


/***********  RENDER  ***********/

React.render(
  <ChatWindow />,
  rootElement
);
