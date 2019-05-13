import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import MainRouter from './modules/MainRouter';
import store from './redux/store';
import {
  fetchCurrentUserIfNeeded,
  fetchProjectsIfNeeded
} from './redux/actions';

const rootElement = document.getElementById('app-root');

ReactDOM.render(
  <Provider store={store}>
    <MainRouter/>
  </Provider>,
  rootElement
);

// Load initial data to make it faster available.
//store.dispatch(fetchProjectsIfNeeded());
//store.dispatch(fetchCurrentUserIfNeeded());
