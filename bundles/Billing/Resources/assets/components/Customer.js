import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import { push } from 'react-router-redux';

const $ = require('jquery');

class Customer extends Component {
  constructor (props) {
    super(props);
  }

  componentDidMount () {
    const {dispatch} = this.props;
  }

  render() {
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
                id="customer-att"
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
