module.exports = {
    "root": true,
    "extends": [
        "standard",
        "plugin:react/recommended"
    ],
    "settings": {
        "react": {
            "version": "detect"
        }
    },
    // Needed to parse arrow functions as react class properties
    // (e.g. `handleSubmit = (event) => { … }`.
    "parser": "babel-eslint",
    "rules": {
        "indent": ["error", 4],
        // Party like it's 1999!
        "semi": ["error", "always"]
    },
    "env": {
        "jquery": true
    }
};
