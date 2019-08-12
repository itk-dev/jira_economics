import reduxApi from "redux-api";
import adapterFetch from "redux-api/lib/adapters/fetch";
export default reduxApi({
    getProject: {
        reducerName: "project",
        url: "/jira/billing/jira_api/project/:id",
    },
    getInvoice: {
        reducerName: "invoice",
        url: "/jira/billing/jira_api/invoice/:id"
    },
    updateInvoice: {
        reducerName: "invoice",
        url: "/jira/billing/jira_api/invoice/:id",
        options: {
            method: "put"
        }
    },
    createInvoice: {
        reducerName: "invoice",
        url: "/jira/billing/jira_api/invoice",
        options: {
            method: "post"
        }
    },
    deleteInvoice: {
        reducerName: "invoice",
        url: "/jira/billing/jira_api/invoice/:id",
        options: {
            method: "delete",
            headers: {
                "Access-Control-Request-Method": "DELETE"
            },
        },
    },
    getInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira/billing/jira_api/invoice_entry/:id"
    },
    updateInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira/billing/jira_api/invoice_entry/:id",
        options: {
            method: "put"
        }
    },
    createInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira/billing/jira_api/invoice_entry",
        options: {
            method : "post"
        }
    },
    deleteInvoiceEntry: {
        reducerName: "invoiceEntry",
        url: "/jira/billing/jira_api/invoice_entry/:id",
        options: {
            method: "delete",
            headers: {
                "Access-Control-Request-Method": "DELETE"
            },
        },
    },
    getInvoices: {
        reducerName: "invoices",
        url: "/jira/billing/jira_api/invoices/:id",
    },
    getAllInvoices: {
        reducerName: "allInvoices",
        url: "/jira/billing/jira_api/invoices_all"
    },
    getInvoiceEntries: {
        reducerName: "invoiceEntries",
        url: "/jira/billing/jira_api/invoice_entries/:id",
    },
    getAllInvoiceEntries: {
        reducerName: "allInvoiceEntries",
        url: "/jira/billing/jira_api/invoice_entries_all"
    },
    getProjects: {
        reducerName: "projects",
        url: "/jira/billing/jira_api/projects",
        cache: { expire: 5 * 60 },
    },
    getCurrentUser: {
        reducerName: "currentUser",
        url: "/jira/billing/jira_api/current_user",
        cache: { expire: 5 * 60 },
    },
    getJiraIssues: {
        reducerName: "jiraIssues",
        url: "/jira/billing/jira_api/jira_issues/:id",
        cache: { expire: 5 * 60 },
    },
    getCustomer: {
        reducerName: "customer",
        url: "/jira/billing/jira_api/customer/:id"
    },
    createCustomer: {
        reducerName: "customer",
        url: "/jira/billing/jira_api/customer",
        options: {
            method : "post"
        }
    },
    updateCustomer: {
        reducerName: "customer",
        url: "/jira/billing/jira_api/customer/:id",
        options: {
            method: "put"
        }
    },
    deleteCustomer: {
        reducerName: "customer",
        url: "/jira/billing/jira_api/customer/:id",
        options: {
            method: "delete",
            headers: {
                "Access-Control-Request-Method": "DELETE"
            },
        },
    },

}).use("fetch", adapterFetch(fetch));