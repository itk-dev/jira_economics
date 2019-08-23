import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

const resources = {
    da: {
        translation: {
            'common.modal.cancel': 'Annullér',
            'common.modal.confirm': 'Bekræft',
            'invoice.choose_project': 'Vælg projekt',
            'invoice.modals.delete_entry.title': 'Slet fakturaindgang',
            'invoice.modals.delete_entry.body': 'Er du sikker på du vil slette denne fakturaindgang?',
            'invoice.new': 'Ny faktura',
            'invoice.invoice_entries_list_title': 'Fakturaindgange',
            'invoice.client_information': 'Klientinformationer',
            'invoice.client_name': 'Klient',
            'invoice.client_contact': 'Kontakt',
            'invoice.client_default_price': 'Standardpris',
            'invoice.edit_entry': 'Redigér indgang',
            'invoice.delete_entry': 'Slét indgang',
            'invoice.form.to_account': 'Til konto',
            'invoice.form.product': 'Titel',
            'invoice.form.description': 'Beskrivelse',
            'invoice.form.amount': 'Antal timer',
            'invoice.form.price': 'Pris pr. time (DKK)',
            'invoice.form.total_price': 'Total pris (DKK)',
            'invoice.add_new_jira_entry': 'Tilføj Jiraindgang',
            'invoice.add_new_manual_entry': 'Tilføj indgang',
            'invoice.record_invoice': 'Bogfør faktura',
            'invoice.delete_invoice': 'Slet faktura',
            'invoice.recorded_false': 'Ikke bogført',
            'invoice.recorded_true': 'Bogført',
            'invoice.invoice_id': 'Faktura: <1>{{ invoiceId }}</1>',
            'invoice.recorded': 'Invoice recorded: <1>{{ invoiceRecorded }}</1>'
        }
    }
};

i18n
    .use(initReactI18next)
    .init({
        resources,
        lng: 'da',
        fallbackLng: 'da',
        keySeparator: false,
        interpolation: {
            escapeValue: false
        }
    });

export default i18n;
