import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

const resources = {
    da: {
        translation: {
            'invoice.new': 'Ny faktura',
            'invoice.choose_project': 'Vælg projekt'
        }
    }
};

i18n
    .use(initReactI18next)
    .init({
        resources,
        lng: 'da',
        fallbackLng: 'da',
        keySeparator: true,
        interpolation: {
            escapeValue: false
        }
    });

export default i18n;
