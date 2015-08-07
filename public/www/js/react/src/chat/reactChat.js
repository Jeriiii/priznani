/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  ZÁVISLOSTI  ***********/
var ProfilePhoto = require('../components/profile').ProfilePhoto;
var MessageActions = require('../flux/actions/chat/MessageActionCreators');
var MessageStore = require('../flux/stores/chat/MessageStore');

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
var usualOlderMessagesCount = reactGetOlderMessages.dataset.maxmessages;
var usualLoadMessagesCount = reactLoadMessages.dataset.maxmessages;

/***********  DEFINICE  ***********/
/** Část okna, která má svislý posuvník - obsahuje zprávy, tlačítko pro donačítání... */
var MessagesWindow = React.createClass({
  getInitialState: function() {
    return {messages: [], thereIsMore: true, href: '' };
  },
  componentDidMount: function() {
    var component = this;
    MessageStore.addChangeListener(function(){
      component.setState(MessageStore.getState());
    });
    MessageActions.createGetInitialMessages(reactLoadMessagesLink, this.props.userCodedId, parametersPrefix, usualLoadMessagesCount);
  },
  render: function() {
    var messages = this.state.messages;
    var oldestId = this.getOldestId(messages);
    /* sestavení odkazu pro tlačítko */
    var moreButtonLink = reactGetOlderMessagesLink + '&' + parametersPrefix + 'lastId=' + oldestId + '&' + parametersPrefix + 'withUserId=' + this.props.userCodedId;
    return (
      <div className="messagesWindow">
        <LoadMoreButton loadHref={moreButtonLink} loadTo={this} oldestId={oldestId} thereIsMore={this.state.thereIsMore} userCodedId={this.props.userCodedId} />
        {messages.map(function(message){
            return <Message key={message.id} messageData={message} userHref={message.profileHref} profilePhotoUrl={message.profilePhotoUrl} />;
        })
        }
      </div>
    );
  },
  getOldestId: function(messages){
    return (messages[0]) ? messages[0].id : 9007199254740991; /*nastavení hodnoty nebo maximální hodnoty, když není*/
  }
});

/** Jedna zpráva. */
var Message = React.createClass({
  render: function() {
    var message = this.props.messageData;
    return (
      <div className="message">
        <ProfilePhoto profileLink={this.props.userHref} userName={message.name} profilePhotoUrl={this.props.profilePhotoUrl} />
        <div className="messageArrow" />
        <p className="messageText">
          {message.text}
          <span className="messageDatetime">{message.sendedDate}</span>
        </p>
        <div className="clear" />
      </div>
    );
  }
});

/** Donačítací tlačítko */
var LoadMoreButton = React.createClass({
  render: function() {
    if(!this.props.thereIsMore){ return null;}
    return (
      <span className="loadMoreButton btn-main loadingbutton ui-btn" onClick={this.handleClick}>
        Načíst další zprávy
      </span>
    );
  },
  handleClick: function(){
    MessageActions.createGetOlderMessages(reactGetOlderMessagesLink, this.props.userCodedId, this.props.oldestId, parametersPrefix, usualOlderMessagesCount);
  }
});

/** Formulář pro odesílání zpráv */
var NewMessageForm = React.createClass({
  render: function() {
    var loggedUser = this.props.loggedUser;
    return (
      <div className="newMessage">
        <ProfilePhoto profileLink={loggedUser.href} userName={loggedUser.name} profilePhotoUrl={loggedUser.profilePhotoUrl} />
        <div className="messageArrow" />
        <form onSubmit={this.onSubmit}>
          <input type="text" className="messageInput" />
          <input type="submit" className="btn-main medium button" value="Odeslat" />
        </form>
      </div>
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
  ChatWindow: React.createClass({
    render: function () {
      return (
        <div className="chatWindow">
          <MessagesWindow userCodedId={this.props.userCodedId} />
          <NewMessageForm loggedUser={this.props.loggedUser} userCodedId={this.props.userCodedId} />
        </div>
      )
    }
  })
};



  /***********  KOMUNIKACE (jQuery) ***********/

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
