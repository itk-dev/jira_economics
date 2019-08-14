import React from 'react';
import PropTypes from 'prop-types';

const ContentWrapper = ({ children }) => {
    return (
        <div className="row">
            <div className="col-12">
                {children}
            </div>
        </div>
    );
};

ContentWrapper.propTypes = {
    children: PropTypes.oneOfType([
        PropTypes.arrayOf(PropTypes.node),
        PropTypes.node
    ])
};

export default ContentWrapper;
