<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\CoreBundle\Event;

final class Events
{
    /**
     * Menu Events.
     */
    const MENU_VIEW = 'symedit.menu.view';

    /**
     * FOS View / Subject Set.
     */
    const SUBJECT_SET = 'symedit.subject.set';

    /**
     * Contact Form.
     */
    const CONTACT_SUBMIT_VALID = 'symedit.contact.valid';

    /**
     * Page Display Options
     */
    const PAGE_DISPLAY_OPTIONS = 'symedit.form.page_display';
}
