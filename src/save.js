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
    const { template, siteKey } = attributes;

    return (
        <div {...useBlockProps.save()} data-sitekey={siteKey}>
            <div>
                <div className='whitepaper-row-wrapper'>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            {__('First Name', 'whitepaper')}
                            <input className="whitepaper-first-name" type="text" placeholder={__('Enter your first name', 'whitepaper')} required />
                        </label>
                    </div>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            {__('Last Name', 'whitepaper')}
                            <input className="whitepaper-last-name" type="text" placeholder={__('Enter your last name', 'whitepaper')} required />
                        </label>
                    </div>
                </div>
                <div className='whitepaper-row-wrapper'>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            {__('Email', 'whitepaper')}
                            <input className="whitepaper-email" type="email" placeholder={__('Enter your email', 'whitepaper')} required />
                        </label>
                    </div>
                </div>
                <div className='whitepaper-row-wrapper whitepaper-row-wrapper-checkbox'>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            <input className="whitepaper-subscribe ct-checkbox" type="checkbox" />
                            {__('Subscribe to newsletter', 'whitepaper')}
                        </label>
                    </div>
                </div>
                <div className='whitepaper-row-wrapper whitepaper-row-wrapper-checkbox'>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            <input className="whitepaper-subscribe ct-checkbox" type="checkbox" required />
                            {__('Accept Terms and Conditions and Privacy Policy', 'whitepaper')}
                        </label>
                    </div>
                </div>
                <div className='whitepaper-row-wrapper'>
                    <div className='whitepaper-input-wrapper'>
                        <input className="whitepaper-type" type="hidden" name="type" value={template} />
                        <button className="whitepaper-submit button" type="submit">{__('Send', 'whitepaper')}</button>
                        <div className="whitepaper-form-messages" style={{ color: 'red' }}></div>
                    </div>
                </div>
            </div>
        </div >
    );
}