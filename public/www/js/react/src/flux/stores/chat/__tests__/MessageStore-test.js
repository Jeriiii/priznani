/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */

jest.dontMock('../MessageStore');
jest.dontMock('object-assign');
jest.autoMockOff();
var ActionTypes = require('../../../constants/ActionConstants');
jest.autoMockOn();


//https://github.com/facebook/jest/issues/17
describe('ChatMessageStore', function() {


  var Dispatcher;
  var MessageStore;
  var callback;

  beforeEach(function() {
    Dispatcher = require('../../../dispatcher/datenodeDispatcher');
    MessageStore = require('../MessageStore');
    callback = Dispatcher.register.mock.calls[0][0];
  });

  /* oveření, že se Store zaregistroval u Dispatcheru */
  it('registers a callback with the dispatcher', function() {
    expect(Dispatcher.register.mock.calls.length).toBe(1);
  });

  /* ověření, že když nepřijdou zprávy, tak se žádné nepřidají */
  it('adds no message into its data when there is not any', function() {
    var action = {
      type: ActionTypes.NO_INITIAL_MESSAGES_ARRIVED
    };
    callback(action);
    expect(MessageStore.getState().messages.length).toBe(0);
  });

  /* ověření, že se přidají donačtené zprávy */
  it('adds older messages', function() {
    var action = {
      type: 'OLDER_MESSAGES_ARRIVED',
      userCodedId: "111",
      usualMessagesCount : 10,
      data: {'111': {
        messages: [
          {id: 1, text: "testtext1"},
          {id: 2, text: "testtext2"},
          {id: 3, text: "testtext3"},
          {id: 4, text: "testtext4"}
        ]
      }}
    };
    callback(action);
    expect(MessageStore.getState().messages.length).toBe(4);
  });

});
