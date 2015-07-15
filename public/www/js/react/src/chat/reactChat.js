/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

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

/** Okno celého chatu s jedním uživatelem */
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

/** Část okna, která má svislý posuvník - obsahuje zprávy, tlačítko pro donačítání... */
var MessagesWindow = React.createClass({
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

/** Komponenta na profilovou fotku */
var ProfilePhoto = React.createClass({
  render: function () {
    return (
      <a className="generatedProfile" href={this.props.profileLink} title={this.props.userName}>
        <img src={this.props.profilePhotoUrl} />
      </a>
    );
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
    getOlderMessages(this.props.loadTo, this.props.userCodedId, this.props.oldestId, prependDataIntoComponent);
  }
});

/** Formulář pro odesílání zpráv */
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
