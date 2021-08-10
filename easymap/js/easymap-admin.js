(function( $ ) {
    'use strict';

    /**
     * easymap-admin.js
     * Copyright (C) 2021 Joaquim Homrighausen <joho@webbplatsen.se>
     * Development sponsored by WebbPlatsen i Sverige AB, www.webbplatsen.se
     *
     * This file is part of EasyMap. EasyMap is free software.
     *
     * You may redistribute it and/or modify it under the terms of the
     * GNU General Public License version 2, as published by the Free Software
     * Foundation.
     *
     * EasyMap is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
     * See the GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with the EasyMap package. If not, write to:
     *  The Free Software Foundation, Inc.,
     *  51 Franklin Street, Fifth Floor
     *  Boston, MA  02110-1301, USA.
     */
    var formTabE = null;

    /* Handle location edit DIV resize */
    const easymapLocationObserver = new ResizeObserver(entries => {
        for (let entry of entries) {
            /*console.log(entry);*/
            easymapLocSec.style.width = ( entry.contentRect.width ) + 'px';
            easymapLocSec.style.opacity = '1';
        }
    });

    var easymapAllTabs, easymapAllTabContent, easymapLocPri, easymapLocSec;
    /* Used to create event handlers with better context (for strict)*/
    function easymapPartial(fn, arg) {
        return function() {
            return (fn.call(this, arg));
        };
    }
    /* Toggle settings tabs*/
    function easymapSettingTabClick(e) {
        /*console.log(e);*/
        if (! e.classList.contains('nav-tab-active')) {
            Array.from(easymapAllTabs).forEach(function(tab) {
                tab.classList.remove('nav-tab-active');
            });
            Array.from(easymapAllTabContent).forEach(function(tab) {
                tab.classList.remove('easymap-is-visible-block');
                tab.classList.add('easymap-is-hidden');
            });
            e.classList.add('nav-tab-active');
            let linkAnchor = e.getAttribute('data-toggle');
            var visibleBlock = document.getElementById(linkAnchor);
            if (visibleBlock !== null) {
                visibleBlock.classList.add('easymap-is-visible-block');
                formTabE.value = e.href.substring(e.href.indexOf("#")+1);
            } else {
                console.log('Unable to fetch ID for '+e.getAttribute('data-toggle'));
            }
        }
    }
    /* Make tab visible */
    function easymapShowTab(e) {
        if (e != null) {
            e.classList.add('nav-tab-active');
            let visibleBlock = document.getElementById(e.getAttribute('data-toggle'));
            if (visibleBlock !== null) {
                visibleBlock.classList.add('easymap-is-visible-block');
            } else {
                console.log('Unable to fetch ID for '+e.getAttribute('data-toggle'));
            }
        }
    }
    /* Initialize stuff when DOM is ready*/
    var easymapSetup = function() {
        easymapAllTabs = document.getElementsByClassName('easymap-tab');
        easymapAllTabContent = document.getElementsByClassName('easymap-tab-content');
        formTabE = document.getElementById('easymap-form-tab');//Allow form override
        let isFirstTab = true;
        let firstElement = null;
        let formTabV = '';
        if (formTabE !== null) {
            formTabV = formTabE.value;
        }
        /*console.log(window.location);*/
        Array.from(easymapAllTabs).forEach(function(e) {
            if (firstElement === null) {
                firstElement = e;
            }
            e.addEventListener('click', easymapPartial(easymapSettingTabClick, e));
            if (formTabE !== null) {
                if (isFirstTab) {
                    if (('#' + formTabV) == e.hash) {
                        easymapShowTab(e);
                        isFirstTab = false;
                    }
                }
            } else if (! window.location.hash) {
                if (isFirstTab) {
                    easymapShowTab(e);
                    isFirstTab = false;
                }
            } else if (window.location.hash == e.hash) {
                easymapShowTab(e);
                isFirstTab = false;
            }
        });
        if ( isFirstTab ) {
            easymapShowTab(firstElement);
        }
        /* Adjust location edit form(s) if we have any */
        easymapLocPri = document.getElementById('easymap-location-primary');
        easymapLocSec = document.getElementById('easymap-location-secondary');
        if (easymapLocPri && easymapLocPri) {
            /* Re-size to "parent" */
            /* easymapLocSec.style.width = ( easymapLocPri.getBoundingClientRect().width - 12) + 'px'; */
            /* This will be called on load as well */
            easymapLocationObserver.observe(easymapLocPri);
        }
    };
    /* Make sure we are ready */
    if (document.readyState === "complete" ||
          (document.readyState !== "loading" && !document.documentElement.doScroll)) {
      easymapSetup();
    } else {
      document.addEventListener("DOMContentLoaded", easymapSetup);
    }

})( jQuery );
