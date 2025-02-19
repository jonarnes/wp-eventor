const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { 
    PanelBody, 
    TextControl, 
    RangeControl,
    __experimentalNumberControl: NumberControl 
} = wp.components;

registerBlockType('eventor-integration/events', {
    apiVersion: 2,
    title: __('Eventor Events', 'eventor-integration'),
    description: __('Display events from Eventor.', 'eventor-integration'),
    category: 'widgets',
    icon: 'calendar-alt',
    supports: {
        html: false
    },
    
    attributes: {
        organisationIds: {
            type: 'string',
            default: ''
        },
        daysBack: {
            type: 'number',
            default: 0
        },
        daysForward: {
            type: 'number',
            default: 0
        },
        pastEventsCount: {
            type: 'number',
            default: 0
        }
    },
    
    edit: function({ attributes, setAttributes }) {
        const blockProps = useBlockProps();
        
        return (
            <>
                <InspectorControls>
                    <PanelBody 
                        title={__('Event Settings', 'eventor-integration')}
                        initialOpen={true}
                    >
                        <TextControl
                            label={__('Organisation IDs', 'eventor-integration')}
                            help={__('Override default organisation IDs (comma-separated)', 'eventor-integration')}
                            value={attributes.organisationIds}
                            onChange={(value) => setAttributes({ organisationIds: value })}
                        />
                        <RangeControl
                            label={__('Days to look back', 'eventor-integration')}
                            help={__('Override default setting (0 = use plugin default)', 'eventor-integration')}
                            value={attributes.daysBack}
                            onChange={(value) => setAttributes({ daysBack: value })}
                            min={0}
                            max={365}
                        />
                        <RangeControl
                            label={__('Days to look forward', 'eventor-integration')}
                            help={__('Override default setting (0 = use plugin default)', 'eventor-integration')}
                            value={attributes.daysForward}
                            onChange={(value) => setAttributes({ daysForward: value })}
                            min={0}
                            max={365}
                        />
                        <RangeControl
                            label={__('Number of past events', 'eventor-integration')}
                            help={__('Override default setting (0 = use plugin default)', 'eventor-integration')}
                            value={attributes.pastEventsCount}
                            onChange={(value) => setAttributes({ pastEventsCount: value })}
                            min={0}
                            max={10}
                        />
                    </PanelBody>
                </InspectorControls>
                <div { ...blockProps }>
                    <div className="components-placeholder">
                        <div className="components-placeholder__label">
                            { __('Eventor Events', 'eventor-integration') }
                        </div>
                        <div className="components-placeholder__instructions">
                            { attributes.organisationIds ? 
                                __('Showing events for organisations: ', 'eventor-integration') + attributes.organisationIds :
                                __('Using default organisation settings', 'eventor-integration')
                            }
                        </div>
                    </div>
                </div>
            </>
        );
    },

    save: function() {
        return null;
    }
}); 