import PropTypes from 'prop-types';
import React from 'react';
import { Link } from 'react-router';
import {
    GlobalItem,
    LayoutManager,
    NavigationProvider,
} from '@atlaskit/navigation-next';
import GlobalNavigation from '@atlaskit/global-navigation';
import Drawer from '@atlaskit/drawer';
import Icon from '@atlaskit/icon';
import DashboardIcon from '@atlaskit/icon/glyph/dashboard';
import GearIcon from '@atlaskit/icon/glyph/settings';
import SearchIcon from '@atlaskit/icon/glyph/search';
import CreateIcon from '@atlaskit/icon/glyph/add';
import ArrowleftIcon from '@atlaskit/icon/glyph/arrow-left';
import LockIcon from '@atlaskit/icon/glyph/lock';

import CreateDrawer from '../components/CreateDrawer';
import SearchDrawer from '../components/SearchDrawer';
import HelpDropdownMenu from '../components/HelpDropdownMenu';
import AccountDropdownMenu from '../components/AccountDropdownMenu';
import BillingIcon from '@atlaskit/icon/glyph/billing';

const itkInvoicingLogo = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <g fill="none">
            <polygon fill="#FECE60" points="10.294 10.294 21.647 10.294 15.867 21.647 10.294 21.647" transform="rotate(-135 15.97 15.97)"/>
            <polygon fill="#7DBA6D" points="10.294 2.353 21.647 8.039 21.647 13.706 10.294 13.706" transform="rotate(-135 15.97 8.03)"/>
            <polygon fill="#61B7E8" points="8.077 2.353 13.706 2.353 13.706 13.706 2.353 13.706" transform="rotate(-135 8.03 8.03)"/>
            <polygon fill="#D56056" points="2.353 10.294 13.706 10.294 13.706 21.647 2.353 16.041" transform="rotate(-135 8.03 15.97)"/>
            <polygon fill="#FECE60" points="7.941 16 14.765 16 13.347 18.765" transform="rotate(-135 11.353 17.382)"/>
            <polygon fill="#22A136" points="15.94 7.941 13.588 8.748 15.941 16.059"/>
            <polygon fill="#009BDD" points="13.177 5.237 10.705 5.928 13.351 13.177" transform="rotate(-89 12.028 9.207)"/>
            <polygon fill="#C00122" points="10.411 8.001 8.001 8.867 10.527 16.001" transform="rotate(-179 9.264 12)"/>
            <polygon fill="#FBB901" points="13.154 10.824 10.767 11.686 13.293 18.765" transform="rotate(91 12.03 14.795)"/>
        </g>
    </svg>
);

export default class StarterNavigation extends React.Component {
  state = {
    navLinks: [
      ['/', 'Home', DashboardIcon],
      ['/settings', 'Settings', GearIcon],
      ['/login', 'Login', LockIcon],
    ]
  };

  static contextTypes = {
    navOpenState: PropTypes.object,
    router: PropTypes.object,
  };

  openDrawer = (openDrawer) => {
    this.setState({ openDrawer });
  };

  shouldComponentUpdate(nextProps, nextContext) {
    return true;
  };

  render() {
    const backIcon = <ArrowleftIcon label="Back icon" size="medium" />;
    const globalPrimaryIcon = <Icon glyph={itkInvoicingLogo} label="ITK Invoice" size="large" />;

    return (
      <GlobalNavigation
        isOpen={this.context.navOpenState.isOpen}
        width={this.context.navOpenState.width}
        onResize={this.props.onNavResize}
        containerHeaderComponent={() => (
          <AkContainerTitle
            icon={<BillingIcon/>}
            text="Invoicing"
            primaryColor='#fff'
            secondaryColor='#999'
          />
        )}
        globalPrimaryIcon={globalPrimaryIcon}
        globalPrimaryItemHref="/"
        globalSearchIcon={<SearchIcon label="Search icon" />}
        hasBlanket
        drawers={[
          <Drawer
            backIcon={backIcon}
            isOpen={this.state.openDrawer === 'search'}
            key="search"
            onBackButton={() => this.openDrawer(null)}
            primaryIcon={globalPrimaryIcon}
          >
            <SearchDrawer
              onResultClicked={() => this.openDrawer(null)}
              onSearchInputRef={(ref) => {
                this.searchInputRef = ref;
              }}
            />
          </Drawer>,
          <Drawer
            backIcon={backIcon}
            isOpen={this.state.openDrawer === 'create'}
            key="create"
            onBackButton={() => this.openDrawer(null)}
            primaryIcon={globalPrimaryIcon}
          >
            <CreateDrawer
              onItemClicked={() => this.openDrawer(null)}
            />
          </Drawer>
        ]}
        globalAccountItem={AccountDropdownMenu}
        globalCreateIcon={<CreateIcon label="Create icon" />}
        globalHelpItem={HelpDropdownMenu}
        onSearchDrawerOpen={() => this.openDrawer('search')}
        onCreateDrawerOpen={() => this.openDrawer('create')}
      >
        {
          this.state.navLinks.map(link => {
            const [url, title, Icon] = link;
            return (
              <Link key={url} to={url}>
                <AkNavigationItem
                  icon={<Icon label={title} size="medium" />}
                  text={title}
                  isSelected={this.context.router.isActive(url, true)}
                />
              </Link>
            );
          }, this)
        }
      </GlobalNavigation>
    );
  }
}
