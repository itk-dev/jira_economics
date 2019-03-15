import React, {Component} from 'react';
import Avatar from '@atlaskit/avatar';

// Icon imports
import AddIcon from '@atlaskit/icon/glyph/add';
import FolderFilledIcon from '@atlaskit/icon/glyph/folder-filled';
import GraphLineIcon from '@atlaskit/icon/glyph/graph-line';
import QuestionCircleIcon from '@atlaskit/icon/glyph/question-circle';
import SearchIcon from '@atlaskit/icon/glyph/search';
import RoadmapIcon from '@atlaskit/icon/glyph/roadmap';
import Icon from '@atlaskit/icon';

// Grid import
import {gridSize as gridSizeFn} from '@atlaskit/theme';

import {
  GlobalNav,
  GroupHeading,
  Item,
  LayoutManager,
  MenuSection,
  NavigationProvider,
} from '@atlaskit/navigation-next';

const gridSize = gridSizeFn();

const itkSymbol = () => (
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
       viewBox="0 0 24 24">
    <g fill="none">
      <polygon fill="#FECE60"
               points="10.294 10.294 21.647 10.294 15.867 21.647 10.294 21.647"
               transform="rotate(-135 15.97 15.97)"/>
      <polygon fill="#7DBA6D"
               points="10.294 2.353 21.647 8.039 21.647 13.706 10.294 13.706"
               transform="rotate(-135 15.97 8.03)"/>
      <polygon fill="#61B7E8"
               points="8.077 2.353 13.706 2.353 13.706 13.706 2.353 13.706"
               transform="rotate(-135 8.03 8.03)"/>
      <polygon fill="#D56056"
               points="2.353 10.294 13.706 10.294 13.706 21.647 2.353 16.041"
               transform="rotate(-135 8.03 15.97)"/>
      <polygon fill="#FECE60" points="7.941 16 14.765 16 13.347 18.765"
               transform="rotate(-135 11.353 17.382)"/>
      <polygon fill="#22A136" points="15.94 7.941 13.588 8.748 15.941 16.059"/>
      <polygon fill="#009BDD" points="13.177 5.237 10.705 5.928 13.351 13.177"
               transform="rotate(-89 12.028 9.207)"/>
      <polygon fill="#C00122" points="10.411 8.001 8.001 8.867 10.527 16.001"
               transform="rotate(-179 9.264 12)"/>
      <polygon fill="#FBB901" points="13.154 10.824 10.767 11.686 13.293 18.765"
               transform="rotate(91 12.03 14.795)"/>
    </g>
  </svg>
);


/**
 * Global navigation
 */
const globalNavPrimaryItems = [
  {
    id: 'itk',
    icon: () => <Icon glyph={itkSymbol} label="ITK" size="large"/>,
    label: 'ITK',
  },
  {id: 'search', icon: SearchIcon, label: 'Search'},
  {id: 'create', icon: AddIcon, label: 'Add'},
];

const globalNavSecondaryItems = [
  {
    id: '10-composed-navigation',
    icon: QuestionCircleIcon,
    label: 'Help',
    size: 'small',
  },
  {
    id: '10-composed-navigation-2',
    icon: () => (
      <Avatar
        borderColor="transparent"
        isActive={false}
        isHover={false}
        size="small"
      />
    ),
    label: 'Profile',
    size: 'small',
  },
];

const GlobalNavigation = () => (
  <div data-webdriver-test-key="global-navigation">
    <GlobalNav
      primaryItems={globalNavPrimaryItems}
      secondaryItems={globalNavSecondaryItems}
    />
  </div>
);

/**
 * Content navigation
 */

export default class Navigation extends Component {
  constructor(props) {
    super(props);
    this.state = {
      shouldDisplayContainerNav: true,
      dialogOpen: false
    };
  }

  toggleContainerNav = () => {
    this.setState(state => ({
      shouldDisplayContainerNav: !state.shouldDisplayContainerNav,
    }));
  };
  ContainerNavigation = () => (
    <div data-webdriver-test-key="container-navigation">
      <MenuSection>
        {({className}) => (
          <div className={className}>
            <GroupHeading>ITK Jira</GroupHeading>
            <Item
              before={FolderFilledIcon}
              text="Projectslist"
              isSelected
              testKey="container-item-all-projects"
              href="/"
            />
            <Item
              before={GraphLineIcon}
              text="Statistics"
              testKey="container-item-sprintplanning"
              href="/statistics"
            />
            <Item
              before={RoadmapIcon}
              text="Sprintplanning"
              testKey="container-item-sprintplanning"
              href="/sprintplanning"
            />
          </div>
        )}
      </MenuSection>
    </div>
  );

  render() {
    const {shouldDisplayContainerNav} = this.state;
    return (
      <NavigationProvider>
        <LayoutManager
          globalNavigation={GlobalNavigation}
          productNavigation={() => null}
          containerNavigation={
            shouldDisplayContainerNav ? this.ContainerNavigation : null
          }
        >
          <div
            data-webdriver-test-key="content"
            style={{padding: `${gridSize * 4}px ${gridSize * 5}px`}}
          >

          </div>
        </LayoutManager>
      </NavigationProvider>
    );
  }
}
