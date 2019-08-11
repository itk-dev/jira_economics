import React from 'react';

export default (props) => (
    <div className="row">
        <div className="col-12">
            <p className="text-muted">{props.breadcrumb}</p>
            <h1>{props.children}</h1>
            <hr/>
        </div>
    </div>
);
