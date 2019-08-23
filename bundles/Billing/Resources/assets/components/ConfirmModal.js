import React from 'react';
import 'moment-timezone';
import Button from 'react-bootstrap/Button';
import Modal from 'react-bootstrap/Modal';
import PropTypes from 'prop-types';

function ConfirmModal (props) {
    return (
        <Modal show={props.showModal}
            onHide={props.onHide}>
            <Modal.Header>
                <Modal.Title>{props.title}</Modal.Title>
            </Modal.Header>
            <Modal.Body>{props.body}</Modal.Body>
            <Modal.Footer>
                <Button variant="secondary"
                    onClick={props.onCancel}>
                    {props.cancelText}
                </Button>
                <Button variant={props.variant}
                    onClick={props.onConfirm}>
                    {props.confirmText}
                </Button>
            </Modal.Footer>
        </Modal>
    );
}

ConfirmModal.propTypes = {
    showModal: PropTypes.bool.isRequired,
    title: PropTypes.string.isRequired,
    body: PropTypes.oneOfType([
        PropTypes.arrayOf(PropTypes.node),
        PropTypes.node
    ]),
    cancelText: PropTypes.string.isRequired,
    confirmText: PropTypes.string.isRequired,
    onHide: PropTypes.func.isRequired,
    onCancel: PropTypes.func.isRequired,
    onConfirm: PropTypes.func.isRequired,
    variant: PropTypes.string
};

export default ConfirmModal;
