/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 *
 * Tento soubor zastřešuje flux akce související se získáváním zpráv. Také zprostředkovává komunikaci se serverem.
 */

 var dispatcher = require('../../dispatcher/datenodeDispatcher');
 var constants = require('../../constants/ActionConstants');
 var EventEmitter = require('events').EventEmitter;

var ActionTypes = constants.ActionTypes;

module.exports = {  /**
   * Získá ze serveru posledních několik proběhlých zpráv s uživatelem s daným id
   * @param {string} url url, které se ptám na zprávy
   * @param {int} userCodedId kódované id uživatele, se kterým si píšu
   * @param {string} parametersPrefix prefix před parametry v url
   * @param {int} usualLoadMessagesCount  obvyklý počet příchozích zpráv v odpovědi
   */
  createGetInitialMessages: function(url, userCodedId, parametersPrefix, usualLoadMessagesCount){
    var data = {};
  	data[parametersPrefix + 'fromId'] = userCodedId;

    $.getJSON(url, data, function(result){
        if(result.length == 0) {
          dispatcher.dispatch({
            type: ActionTypes.NO_INITIAL_MESSAGES_ARRIVED
          });
        }else{
          dispatcher.dispatch({
            type: ActionTypes.OLDER_MESSAGES_ARRIVED,
            data: result,
            userCodedId : userCodedId,
            usualMessagesCount : usualLoadMessagesCount
            /* tady bych případně přidal další data */
          });
        }
    });
  },

  /**
   * Získá ze serveru několik starších zpráv
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {int}   oldestId id nejstarší zprávy (nejmenší známé id)
   * @param  {string} parametersPrefix prefix před parametry v url
   * @param {int} usualOlderMessagesCount  obvyklý počet příchozích zpráv v odpovědi
   */
  createGetOlderMessages: function(url, userCodedId, oldestId, parametersPrefix, usualOlderMessagesCount){
    var data = {};
  	data[parametersPrefix + 'lastId'] = oldestId;
    data[parametersPrefix + 'withUserId'] = userCodedId;
    $.getJSON(url, data, function(result){
        if(result.length == 0) return;
        dispatcher.dispatch({
          type: ActionTypes.OLDER_MESSAGES_ARRIVED,
          data: result,
          userCodedId : userCodedId,
          oldersId : oldestId,
          usualMessagesCount : usualOlderMessagesCount
        });
    });
  },

  /**
   * Pošle na server zprávu.
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {String} message text zprávy
   */
  createSendMessage: function(url, userCodedId, message){
    var data = {
      to: userCodedId,
      type: 'textMessage',
      text: message
    };
    var json = JSON.stringify(data);
  		$.ajax({
  			dataType: "json",
  			type: 'POST',
  			url: url,
  			data: json,
  			contentType: 'application/json; charset=utf-8',
  			success: function(result){
          dispatcher.dispatch({
            type: ActionTypes.NEW_MESSAGES_ARRIVED,
            data: result,
            userCodedId : userCodedId
          });
        }
  		});
  },

  /**
   * Zeptá se serveru na nové zprávy
   * @param {string} url url, které se ptám na zprávy
   * @param  {int}   userCodedId kódované id uživatele
   * @param  {int} lastId poslední známé id
   * @param  {string} parametersPrefix prefix před parametry v url
   */
  createRefreshMessages: function(url, userCodedId, lastId, parametersPrefix){
    var data = {};
  	data[parametersPrefix + 'lastid'] = lastId;
    $.getJSON(url, data, function(result){
        if(result.length == 0) return;
        dispatcher.dispatch({
          type: ActionTypes.NEW_MESSAGES_ARRIVED,
          data: result,
          userCodedId : userCodedId
        });
    });
  }
};
