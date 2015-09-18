/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

/***********  ZÁVISLOSTI  ***********/
var ProfilePhoto = require('../components/profile').ProfilePhoto;
var MessageConstants = require('../flux/constants/ChatConstants').MessageConstants;
var MessageActions = require('../flux/actions/chat/MessageActionCreators');
var MessageStore = require('../flux/stores/chat/MessageStore');
var TimerFactory = require('../components/timer');/* je v cachi, nebude se vytvářet vícekrát */

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
/* časovač pro pravidelné požadavky na server */
var Timer = TimerFactory.newInstance();

/***********  DEFINICE  ***********/
/** Část okna, která má svislý posuvník - obsahuje zprávy, tlačítko pro donačítání... */
var MessagesWindow = React.createClass({
  getInitialState: function() {
    return {messages: [], infoMessages: [], thereIsMore: true, href: '' };
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
    var infoMessages = this.state.infoMessages;
    var oldestId = this.getOldestId(messages);
    var userCodedId = this.props.userCodedId;
    /* sestavení odkazu pro tlačítko */
    var moreButtonLink = reactGetOlderMessagesLink + '&' + parametersPrefix + 'lastId=' + oldestId + '&' + parametersPrefix + 'withUserId=' + this.props.userCodedId;
    return (
      <div className="messagesWindow">
        <LoadMoreButton loadHref={moreButtonLink} oldestId={oldestId} thereIsMore={this.state.thereIsMore} userCodedId={userCodedId} />
        {messages.map(function(message, i){
            return <Message key={userCodedId + 'message' + i} messageData={message} userHref={message.profileHref} profilePhotoUrl={message.profilePhotoUrl} />;
        })
        }
        {infoMessages.map(function(message, i){
              return <InfoMessage key={userCodedId + 'info' + i} messageData={message} />;
          })
        }
      </div>
    );
  },
  getOldestId: function(messages){
    return (messages[0]) ? messages[0].id : 9007199254740991; /*nastavení hodnoty nebo maximální hodnoty, když není*/
  }
});

var InfoMessage = React.createClass({
  render: function(){
      return(<span className="info-message">{this.props.messageData.text}</span>);
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
          {message.images.map(function(image, i){
                return <img src={image.url} width={image.width} key={message.id + 'message' + i} />;
            })
          }
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
        Načíst předchozí zprávy
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
    var slapButton = '';
    if (loggedUser.allowedToSlap){
      slapButton = <a href="#" title="Poslat facku" className="sendSlap" onClick={this.sendSlap}></a>
    }
    return (
      <div className="newMessage">
        <ProfilePhoto profileLink={loggedUser.href} userName={loggedUser.name} profilePhotoUrl={loggedUser.profilePhotoUrl} />
        <div className="messageArrow" />
        <form onSubmit={this.onSubmit}>
          <div className="messageInputContainer">
            <input type="text" className="messageInput" />
            <div className="inputInterface">
              {slapButton}
            </div>
            <div className="clear"></div>
          </div>
          <input type="submit" className="btn-main medium button" value="Odeslat" />
        </form>
      </div>
    );
  },
  sendSlap: function(e){
    e.preventDefault();
    MessageActions.createSendMessage(reactSendMessageLink, this.props.userCodedId, MessageConstants.SEND_SLAP, getLastId());
  },
  onSubmit: function(e){/* Vezme zprávu ze submitu a pošle ji. Také smaže zprávu napsanou v inputu. */
    e.preventDefault();
    var input = e.target.getElementsByClassName('messageInput')[0];
    var message = input.value;
    if(message == undefined || message.trim() == '') return;
    input.value = '';
    MessageActions.createSendMessage(reactSendMessageLink, this.props.userCodedId, message, getLastId());
  }
});

/**
 * inicializuje časovač pravidelně se dotazující na nové zprávy v závislosti na tom, jak se mění data v MessageStore
 * @param {string} userCodedId kódované id uživatele, se kterým si píšu
 */
var initializeChatTimer = function(userCodedId){
  MessageStore.addChangeListener(function(){
    var state = MessageStore.getState();
    if(state.dataVersion == 1){/* data se poprvé změnila */
      Timer.maximumInterval = 60000;
      Timer.initialInterval = 3000;
      Timer.intervalIncrase = 2000;
      Timer.lastId = getLastId();
      Timer.tick = function(){
        MessageActions.createRefreshMessages(reactRefreshMessagesLink, userCodedId, Timer.lastId, parametersPrefix);
      };
      Timer.start();
    }else{/* když se data nezměnila poprvé, ale určitě se změnila */
      Timer.lastId = getLastId();
      Timer.resetTime();
    }
  });

};

/**
 * Vrátí poslední známé id
 * @return {int} posledni známé id
 */
var getLastId = function() {
  var state = MessageStore.getState();
  if(state.messages.length > 0){
    return state.messages[state.messages.length - 1].id;
  }else{
    return 0;
  }
}

module.exports = {
  /** Okno celého chatu s jedním uživatelem */
  ChatWindow: React.createClass({
    componentDidMount: function() {
      initializeChatTimer(this.props.userCodedId);
      MessageActions.reloadWindowUnload();
    },
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
