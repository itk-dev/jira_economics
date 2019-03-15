import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import MainRouter from './modules/MainRouter';
import store from './redux/store';
import { fetchCurrentUser, fetchProjects } from './redux/actions';

const rootElement = document.getElementById('app-root');

ReactDOM.render(
  <Provider store={store}>
    <MainRouter/>
  </Provider>,
  rootElement);

// Fetch projects.
// @TODO: Move to project list and only fetch when old data.
store.dispatch(fetchProjects());

store.dispatch(fetchCurrentUser());
