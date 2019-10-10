import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import MainRouter from './modules/MainRouter';
import store from './redux/store';
import './i18n';
import Flash from './components/Flash';

const rootElement = document.getElementById('app-root');

ReactDOM.render(
    <Provider store={store}>
        <Flash />
        <MainRouter/>
    </Provider>,
    rootElement
);
