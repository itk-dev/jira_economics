import reduxApi from "redux-api";
import adapterFetch from "redux-api/lib/adapters/fetch";
export default reduxApi({
    getProject: {
        reducerName: "project",
        url: "/billing/jira_api/project/:id",
    },
    getInvoice: {
        reducerName: "invoice",
        url: "/billing/jira_api/invoice/:id"
    },
    updateInvoice: {
        reducerName: "invoice",
        url: "/billing/jira_api/invoice/:id",
        options: {
            method: "put"
        }
    },
    createInvoice: {
        reducerName: "invoice",
        url: "/billing/jira_api/invoice",
        options: {
            method: "post"
        }
    },
    deleteInvoice: {
        reducerName: "invoice",
        url: "/billing/jira_api/invoice/:id",
        options: {
            method: "delete",
            headers: {
                "Access-Control-Request-Method": "DELETE"
            },
        },
    },
    getInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/billing/jira_api/invoice_entry/:id"
    },
    updateInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/billing/jira_api/invoice_entry/:id",
        options: {
            method: "put"
        }
    },
    createInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/billing/jira_api/invoice_entry",
        options: {
            method : "post"
        }
    },
    deleteInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/billing/jira_api/invoice_entry/:id",
        options: {
            method: "delete",
            headers: {
                "Access-Control-Request-Method": "DELETE"
            },
        },
    },
    getInvoices: {
        reducerName: "invoices",
        url: "/billing/jira_api/invoices/:id",
    },
    getInvoiceEntries: {
        reducerName: "invoiceEntries",
        url: "/billing/jira_api/invoice_entries/:id",
    },
    getProjects: {
        reducerName: "projects",
        url: "/billing/jira_api/projects",
        cache: { expire: 5 * 60 },
    },
    getCurrentUser: {
        reducerName: "currentUser",
        url: "/billing/jira_api/current_user",
        cache: { expire: 5 * 60 },
    },
    getJiraIssues: {
        reducerName: "jiraIssues",
        url: "/billing/jira_api/jira_issues/:id",
        cache: { expire: 5 * 60 },
    },
}).use("fetch", adapterFetch(fetch));