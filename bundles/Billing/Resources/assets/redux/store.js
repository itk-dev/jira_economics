import thunkMiddleware from 'redux-thunk';
import { createLogger } from 'redux-logger';
import { createStore, applyMiddleware } from 'redux';
import { routerMiddleware, routerReducer} from 'react-router-redux';
import { browserHistory } from 'react-router';
import rootReducer from './reducers';

const loggerMiddleware = createLogger();

const store = createStore(
  rootReducer,
  applyMiddleware(
    thunkMiddleware, // lets us dispatch() functions
    loggerMiddleware, // middleware that logs actions
    routerMiddleware(browserHistory)
  ));

export default store;
