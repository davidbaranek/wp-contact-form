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

import { PanelBody, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { template } = attributes;

    return (
        <div { ...useBlockProps() }>
            <InspectorControls>
                <PanelBody title={__('Settings', 'whitepaper')} initialOpen={true}>
                    <SelectControl
                        label={__('Product', 'whitepaper')}
                        value={ attributes.template }
                        options={[
                            { label: 'Dentapreg', value: 'dentapreg' },
                            { label: 'Fibrafill', value: 'fibrafill' },
                        ]}
                        onChange={(newType) => setAttributes({ template: newType })}
                    />
                </PanelBody>
            </InspectorControls>
            <div>
                <div className='whitepaper-row-wrapper'>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            {__('First Name', 'whitepaper')}
                            <input required className="whitepaper-first-name" type="text" placeholder={__('Enter your first name', 'whitepaper')} />
                        </label>
                    </div>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            {__('Last Name', 'whitepaper')}
                            <input required className="whitepaper-last-name" type="text" placeholder={__('Enter your last name', 'whitepaper')} />
                        </label>
                    </div>
                </div>
                <div className='whitepaper-row-wrapper'>
                    <div className='whitepaper-input-wrapper'>
                        <label>
                            {__('Email', 'whitepaper')}
                            <input required className="whitepaper-email" type="email" placeholder={__('Enter your email', 'whitepaper')} />
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
                            <input required className="whitepaper-subscribe ct-checkbox" type="checkbox" />
                            {__('Accept Terms and Conditions and Privacy Policy', 'whitepaper')}
                        </label>
                    </div>
                </div>
                <div className='whitepaper-row-wrapper'>
                    <div className='whitepaper-input-wrapper'>
                        <input className="whitepaper-type" type="hidden" name="type" value={attributes.template} />
                        <button className="whitepaper-submit" type="submit">{__('Send', 'whitepaper')}</button>
                        <div className="whitepaper-form-messages" style={{ color: 'red' }}></div>
                    </div>
                </div>
            </div>
        </div>
    );
}