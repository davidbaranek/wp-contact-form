/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

import { __ } from '@wordpress/i18n';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
    const { siteKey } = attributes;

    return (
        <div {...useBlockProps.save()} data-sitekey={siteKey}>
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