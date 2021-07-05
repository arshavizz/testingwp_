import { registerBlockType } from '@wordpress/blocks';
import { RichText, InspectorControls, BlockControls, AlignmentToolbar } from '@wordpress/block-editor';
import { Disabled, PanelBody, PanelRow, SelectControl } from '@wordpress/components';
import { ServerSideRender } from '@wordpress/editor';
import { Component, Fragment } from '@wordpress/element';
import { withSelect, select } from '@wordpress/data';
import { __, _e } from '@wordpress/i18n';

const plugin = 'acf-frontend-form-element';

class FirstBlockEdit extends Component {

    constructor(props) {
		super(props);
		this.state = {
			editMode: true
		}
	}
 
	getInspectorControls = () => {
		const { attributes, setAttributes } = this.props;
 
        
        let choices = [];
        if (this.props.posts) {
            choices.push({ value: 0, label: __( 'Select a form', plugin ) });
            this.props.posts.forEach(post => {
                choices.push({ value: post.id, label: post.title.rendered });
            });
        } else {
            choices.push({ value: 0, label: __( 'Loading...', plugin ) })
        }

		return (
			<InspectorControls>
                <PanelBody
						title={__("Form Settings", plugin )}
						initialOpen={true}
					>
						<PanelRow>
                        <SelectControl
                            label={__('Form', plugin )}
                            options={choices}
                            value={attributes.formID}
                            onChange={(newval) => setAttributes({ formID: parseInt(newval) })}
                        />
                        </PanelRow>
                </PanelBody>
			</InspectorControls>
		);
	}
 
/* 	getBlockControls = () => {
        const { attributes, setAttributes } = this.props;
        return (
            <BlockControls>
                
            </BlockControls>
        );
    } */
 
	render() {
        console.log(this.props.posts);

        const { attributes, setAttributes } = this.props;
        const alignmentClass = (attributes.textAlignment != null) ? 'has-text-align-' + attributes.textAlignment : '';
     
        return ([
            this.getInspectorControls(),
            //this.getBlockControls(),
            <Disabled>
            <ServerSideRender
            block={this.props.name}
            attributes={{ 
                formID: attributes.formID,
                editMode: true
            }}
            />
            </Disabled>

        ]);
    }
}
 
registerBlockType('acf-frontend/form', {
	title: __('ACF Frontend Form', plugin ),
	category: 'widgets',
	icon: 'feedback',
	description: __('Display a frontend admin form so that your users can update content from the frontend.', plugin ),
	keywords: ['frontend editing', 'admin form'],
	attributes: {
        formID: {
            type: 'number'
        }
    },
	edit: withSelect(select => {
        const query = {
            per_page: -1,
            status: 'any',
        }
        return {
            posts: select('core').getEntityRecords('postType', 'acf_frontend_form', query)
        }
    })(FirstBlockEdit),
    save: () => { return null }

});