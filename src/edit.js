/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

export default function Edit({ attributes }) {

    return (
        <div {...useBlockProps.save()}>
            <div>
                <div className='contact-form-row-wrapper'>
                    <div className='contact-form-input-wrapper'>
                        <label>
                            {__('First Name', 'bardav')}
                            <input required className="contact-form-first-name" type="text" placeholder={__('Enter your first name', 'bardav')} />
                        </label>
                    </div>
                    <div className='contact-form-input-wrapper'>
                        <label>
                            {__('Last Name', 'bardav')}
                            <input required className="contact-form-last-name" type="text" placeholder={__('Enter your last name', 'bardav')} />
                        </label>
                    </div>
                </div>
                <div className='contact-form-row-wrapper'>
                    <div className='contact-form-input-wrapper'>
                        <label>
                            {__('Email', 'bardav')}
                            <input required className="contact-form-email" type="email" placeholder={__('Enter your email', 'bardav')} />
                        </label>
                    </div>
                </div>
                <div className='contact-form-row-wrapper'>
                    <div className='contact-form-input-wrapper'>
                        <label>
                            {__('Message', 'bardav')}
                            <textarea required className="contact-form-message" type="text" placeholder={__('Write your message', 'bardav')} />
                        </label>
                    </div>
                </div>
                <div className='contact-form-row-wrapper contact-form-row-wrapper-checkbox'>
                    <div className='contact-form-input-wrapper'>
                        <label>
                            <input required className="contact-form-subscribe ct-checkbox" type="checkbox" />
                            {__('Accept Terms and Conditions and Privacy Policy', 'bardav')}
                        </label>
                    </div>
                </div>
                <div className='contact-form-row-wrapper'>
                    <div className='contact-form-input-wrapper'>
                        <button className="contact-form-submit wp-element-button" type="submit">{__('Send', 'bardav')}</button>
                        <div className="contact-form-form-messages" style={{ color: 'red' }}></div>
                    </div>
                </div>
            </div>
        </div>
    );
}