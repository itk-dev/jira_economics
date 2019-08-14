import React from 'react';
import PropTypes from 'prop-types';

const ContentFooter = ({ children }) => {
    return (
        <div className="bg-light text-muted rounded p-3 mt-3">
            {children}
        </div>
    );
};

ContentFooter.propTypes = {
    children: PropTypes.oneOfType([
        PropTypes.arrayOf(PropTypes.node),
        PropTypes.node
    ])
};

export default ContentFooter;
