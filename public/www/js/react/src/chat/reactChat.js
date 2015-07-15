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

var ChatWindow = React.createClass({
  render: function () {
    return (
      <div className="chatWindow">
        <MessagesWindow userCodedId={this.props.userCodedId} />
        <NewMessageForm />
      </div>
    )
  }
});

var MessagesWindow = React.createClass({
  getInitialState: function() {
    return {messages: [] };
  },
  componentDidMount: function() {
    getInitialMessages(this, this.props.userCodedId, setDataIntoComponent);
  },
  render: function() {
    var messages = this.state.messages;
    return (
      <div className="messagesWindow">
        <LoadMoreButton />
        {messages.map(function(message){
            return <Message key={message.id} messageData={message} />;
        })
        }
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
    var message = this.props.messageData;
    return (
      <div className="message">
        <ProfilePhoto profileLink="#" userName="Leopold" profilePhotoUrl="http://localhost/priznani/public/www/images/users/man.jpg" />
        <div className="messageArrow" />
        <p className="messageText">
          {message.text}
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
