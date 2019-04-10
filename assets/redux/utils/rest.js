import reduxApi from "redux-api";
import adapterFetch from "redux-api/lib/adapters/fetch";
export default reduxApi({
    project: "/jira_api/project/:id",
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
    }
}).use("fetch", adapterFetch(fetch));