import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import rest from '../redux/utils/rest';

class Customer extends Component {
    constructor (props) {
        super(props);
    }

    handleSubmitCustomer = (event) => {
        event.preventDefault();
        const {dispatch} = this.props;
        const name = $('#customer-name').val();
        const att = $('#customer-att').val();
        const cvr = $('#customer-cvr').val();
        const ean = $('#customer-ean').val();
        const debtor = $('#customer-debtor').val();
        const customerData = {
            name,
            att,
            cvr,
            ean,
            debtor
        };
        dispatch(rest.actions.createCustomer({}, {
            body: JSON.stringify(customerData)
        }));
    };

    render () {
        return (
            <ContentWrapper>
                <PageTitle>Kundedata</PageTitle>
                <div>
                    <form>
                        <div>
                            <label htmlFor="kundenavn">
                                Kundenavn
                            </label>
                            <input
                                type="text"
                                name="enterKundenavn"
                                className="form-control"
                                id="customer-name"
                                aria-describedby="enterKundenavn"
                                placeholder="Kundens navn">
                            </input>
                            <label htmlFor="kundeatt">
                                Att.
                            </label>
                            <input
                                type="text"
                                name="enterKundeAtt"
                                className="form-control"
                                id="customer-att"
                                aria-describedby="enterKundeAtt"
                                placeholder="Kundens kontaktperson">
                            </input>
                            <label htmlFor="kundecvr">
                                CVR.
                            </label>
                            <input
                                type="text"
                                name="enterKundeCvr"
                                className="form-control"
                                id="customer-cvr"
                                aria-describedby="enterKundeCvr"
                                placeholder="Kundens CVR.nr.">
                            </input>
                            <label htmlFor="kundeean">
                                EAN.
                            </label>
                            <input
                                type="text"
                                name="enterKundeEan"
                                className="form-control"
                                id="customer-ean"
                                aria-describedby="enterKundeEan"
                                placeholder="Kundens EAN.nr.">
                            </input>
                            <label htmlFor="kundedebtor">
                                Debitornr.
                            </label>
                            <input
                                type="text"
                                name="enterKundeDebitor"
                                className="form-control"
                                id="customer-debtor"
                                aria-describedby="enterKundeDebitor"
                                placeholder="Kundens EAN.nr.">
                            </input>
                        </div>
                    </form>
                    <form id="submitForm" onSubmit={this.handleSubmitCustomer}>
                        <button type="submit" className="btn btn-primary"
                                id="submit">Opret kunde
                        </button>
                    </form>
                </div>
            </ContentWrapper>
        );
    }
}

const mapStateToProps = state => {
    return {};
};

export default connect(
    mapStateToProps
)(Customer);
