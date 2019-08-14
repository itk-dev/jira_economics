import React from 'react';
import PropTypes from 'prop-types';

const ProjectTitle = (props) => (
    <div className="row">
        <div className="col-12">
            <p className="text-muted">{props.breadcrumb}</p>
            <h1>{props.children}</h1>
            <hr/>
        </div>
    </div>
);

ProjectTitle.propTypes = {
    children: PropTypes.oneOfType([
        PropTypes.arrayOf(PropTypes.node),
        PropTypes.node
    ]),
    breadcrumb: PropTypes.string
};

export default ProjectTitle;
