import React, { useEffect, useState } from 'react';
import Bus from '../modules/Bus';

const Flash = () => {
    let [visibility, setVisibility] = useState(false);
    let [message, setMessage] = useState('');
    let [type, setType] = useState('');

    useEffect(() => {
        Bus.addListener('flash', ({ message, type }) => {
            setVisibility(true);
            setMessage(message);
            setType(type);
        });
    }, []);

    useEffect(() => {
        if (document.querySelector('.close') !== null) {
            document.querySelector('.close').addEventListener('click', () => setVisibility(false));
        }
    });

    return (
        visibility && <div className={`alert alert-${type}`} role={'alert'}>
            <span className="close"><strong>X</strong></span>
            <p>{message}</p>
        </div>
    );
};

export default Flash;
