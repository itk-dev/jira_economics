'use strict';

// This is a modified version of 'redux-api/lib/adapters/fetch'.

Object.defineProperty(exports, '__esModule', {
    value: true
});

exports.default = function (fetch) {
    return function (url, opts) {
        return fetch(url, opts).then(function (resp) {
            // Normalize IE9's response to HTTP 204 when Win error 1223.
            var status = resp.status === 1223 ? 204 : resp.status;
            var statusText = resp.status === 1223 ? 'No Content' : resp.statusText;

            if (status >= 400) {
                // Ignored since this is a modification of 'redux-api/lib/adapters/fetch', to include resp.
                // eslint-disable-next-line prefer-promise-reject-errors
                return Promise.reject({ status: status, statusText: statusText, resp: resp });
            } else {
                return toJSON(resp).then(function (data) {
                    if (status >= 200 && status < 300) {
                        return data;
                    } else {
                        return Promise.reject(data);
                    }
                });
            }
        });
    };
};

function processData (data) {
    try {
        return JSON.parse(data);
    } catch (err) {
        return data;
    }
}

function toJSON (resp) {
    if (resp.text) {
        return resp.text().then(processData);
    } else if (resp instanceof Promise) {
        return resp.then(processData);
    } else {
        return Promise.resolve(resp).then(processData);
    }
}

module.exports = exports['default'];
