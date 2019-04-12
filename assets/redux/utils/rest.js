import reduxApi from "redux-api";
import adapterFetch from "redux-api/lib/adapters/fetch";
export default reduxApi({
    getProject: {
        reducerName: "project",
        url: "/jira_api/project/:id",
    },
    getInvoice: {
        reducerName: "invoice",
        url: "/jira_api/invoice/:id"
    },
    updateInvoice: {
        reducerName: "invoice",
        url: "/jira_api/invoice/:id",
        options: {
            method: "put"
        }
    },
    getInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira_api/invoice_entry/:id"
    },
    updateInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira_api/invoice_entry/:id",
        options: {
            method: "put"
        }
    },
    createInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira_api/invoice_entry",
        options: {
            method : "post"
        }
    },
    getInvoices: {
        reducerName: "invoices",
        url: "/jira_api/invoices/:id",
    },
    getInvoiceEntries: {
        reducerName: "invoiceEntries",
        url: "/jira_api/invoice_entries/:id",
    },
    getProjects: {
        reducerName: "projects",
        url: "jira_api/projects",
        cache: { expire: 5 * 60 },
    },
    getCurrentUser: {
        reducerName: "currentUser",
        url: "jira_api/current_user",
        cache: { expire: 5 * 60 },
    },
}).use("fetch", adapterFetch(fetch));