/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */
jest.dontMock('../MessageStore');
jest.dontMock('object-assign');

describe('ChatMessageStore', function() {

  var Dispatcher;
  var MessageStore;
  var ActionTypes;
  var callback;

  beforeEach(function() {
    Dispatcher = require('../../../dispatcher/datenodeDispatcher');
    MessageStore = require('../MessageStore');
    ActionTypes = require('../../../constants/ActionConstants').ActionTypess;
    callback = Dispatcher.register.mock.calls[0][0];
  });

  /* oveření, že se Store zaregistroval u Dispatcheru */
  it('registers a callback with the dispatcher', function() {
    expect(Dispatcher.register.mock.calls.length).toBe(1);
  });

  /* ověření, že když nepřijdou zprávy, tak se žádné nepřidají */
  it('adds no message into its data when there is not any', function() {
    // var action = {
    //   type: ActionTypess.test
    // };
    // callback(action);
    // expect(MessageStore.getState().messages.length).toBe(0);
  });

});
