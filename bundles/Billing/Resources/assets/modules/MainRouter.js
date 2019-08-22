import React from 'react';
import { BrowserRouter as Router, Route } from 'react-router-dom';
import HomePage from '../pages/HomePage';
import Invoice from '../pages/Invoice';
import InvoiceEntry from '../pages/InvoiceEntry';
import NewInvoice from '../pages/NewInvoice';

function MainRouter () {
    return (
        <Router basename={'/jira/billing'}>
            <Route exact path="/" component={HomePage}/>
            <Route exact path="/new" component={NewInvoice}/>
            <Route exact path="/project/:projectId/:invoiceId"
                component={Invoice}/>
            <Route exact
                path="/project/:projectId/:invoiceId/:invoiceEntryId"
                component={InvoiceEntry}/>
        </Router>
    );
}

export default MainRouter;
