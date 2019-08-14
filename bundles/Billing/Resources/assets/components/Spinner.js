import React from 'react';

const Spinner = (props) => (
    <div className="spinner-border" style={{
        width: '3rem',
        height: '3rem',
        role: 'status'
    }}>
        <span className="sr-only">Loading invoices...</span>
    </div>
);

export default Spinner;
