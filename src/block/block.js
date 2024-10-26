/**
 * BLOCK: liveblog24-live-blogging-tool
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';
import moment from 'moment';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */

registerBlockType( 'cgb/block-liveblog24-live-blogging-tool', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( '24liveblog' ), // Block title.
    icon: 'list-view', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'lb24-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __( 'lb-24liveblog' ),
        __( 'CGB Example' ),
        __( 'create-guten-block' ),
    ],
    attributes: {
        userId: {
            default: lb24BlockData.getWpUserId,
        },
        token: {
            default: lb24BlockData.getLb24Token,
        },
        uid: {
            default: lb24BlockData.getLb24Uid,
        },
        eid: {
            default: '',
        },
        cover: {
            default: '',
        },
        title: {
            default: '',
        },
        eventList: {
            default: [],
        },
        eventCount: {
            default: 0,
        },
        eventSettings: {
            default: {},
        },
        loading: {
            default: false,
        },
        searchInputValue: {
            default: '',
        },
        currentPage: {
            default: 1,
        },
    },
    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     *
     * @param {Object} props Props.
     * @returns {Mixed} JSX Component.
     */
    edit: ( props ) => {
        const { attributes: {userId, token, uid, eid, cover, title, eventList, eventCount, 
            eventSettings, loading, searchInputValue, currentPage}, setAttributes} = props;

        const lb24GetEvents = (pageAction) => {
            let curPage = currentPage;
            const limit = 3;
            if (pageAction === 'next') {
                if ((limit*curPage) < eventCount) {
                    curPage += 1;
                }
            } else if (pageAction === 'previous' && curPage !== 1) {
                curPage -= 1;
            } else {
                curPage = 1;
            }

            if (pageAction === 'new') {
                setAttributes({searchInputValue: ''})
            }
            const modal = document.getElementById("lb24-wp-event-list-modal")
            modal.style.visibility = 'visible'
            setAttributes({eventList: [], loading: true, currentPage: curPage})
            const url = "https://update.24liveplus.com/v1/update_server/user/" + uid +
                "/events/?limit=" + limit.toString() + "&offset=" + (limit*(curPage-1)).toString() + 
                "&order=1&tid=&query=" + searchInputValue;
            window.fetch(url, {
                method: 'GET',
                headers: {
                    'token': token,
                },
            }).then((response) => {
                const res = response.json();
                return res;
            }).then(res => {
                if (res['err_code'] === 100) {
                    const { data: { count, events }} = res;
                    setAttributes({
                        eventCount: count, eventList: events, loading: false,
                            eventSettings: events.reduce((acc, cur) => {
                                acc[cur.eid] = {
                                    embedType: 'embed', width: '100%', height: '960px'
                                }
                                return acc
                            }, {})
                        })
                } else if ([201, 202, 203, 204].includes(res['err_code'])) {
                    const data = {
                        _ajax_nonce: lb24BlockData.getNonce,
                        action: 'update_lb24_token',
                        user_id: userId,
                        user_token: '',
                        user_uid: '',
                        user_refresh_token: '',
                        user_uname: ''
                    };
                    jQuery.post(ajaxurl, data, function (response) {
                        setAttributes({token: ''});
                        lb24CloseModal();
                    });
                }
            }).catch(res => {
                alert('Server Error');
            }).finally(res => {
                setAttributes({loading: false});
            });
        }

        const lb24CloseModal = () => {
            const modal = document.getElementById("lb24-wp-event-list-modal")
            modal.style.visibility = 'hidden'
            setAttributes({searchInputValue: ''})
        }

        const lb24SetCode = (eid, cover, title) => {
            setAttributes({eid, cover, title});
            lb24CloseModal();
        } 

        const lb24ChangeEmbedType = (eid, e) => {
            setAttributes({eventSettings: {...eventSettings, [eid]: {
                ...eventSettings[eid], embedType: e.target.value}}})
        }

        const lb24ChangeWidth = (eid, e) => {
            setAttributes({eventSettings: {...eventSettings, [eid]: {
                ...eventSettings[eid], width: e.target.value}}})
        }

        const lb24ChangeHeight = (eid, e) => {
            setAttributes({eventSettings: {...eventSettings, [eid]: {
                ...eventSettings[eid], height: e.target.value}}})
        }

        return (
            <div>
                {token ?
                    <div>
                    {eid ?
                        <div className="lb24-wp-edit-event">
                            <img className="lb24-wp-logo" src={lb24BlockData.getLb24Config.LB24_LOGO_ICON}/>
                            <div className="lb24-wp-event-body">
                                <div
                                    className="lb24-wp-selected-cover"
                                    style={{
                                        backgroundImage: `url(${cover})`,
                                        backgroundSize: 'cover',
                                        backgroundPosition: 'center center',
                                        backgroundRepeat: 'no-repeat',
                                    }}
                                >
                                    <a target="_blank" href={`${lb24BlockData.getLb24Config.LB24_URL}/#/event/${eid}/news/add`}>
                                        <span className="lb24-wp-view">View</span>
                                    </a>
                                    <a target="_blank" href={`${lb24BlockData.getLb24Config.LB24_URL}/#/event/${eid}/edit`}>
                                        <span className="lb24-wp-edit">Edit</span>
                                    </a>
                                </div>
                                <p className="lb24-wp-title">{ title }</p>
                            </div>
                        </div>
                        :
                        <div id="lb24-select" className="lb24-wp-loggedin">
                            <img className="lb24-wp-loggedin-logo" src={lb24BlockData.getLb24Config.LB24_LOGO_ICON}/>
                            <button 
                                onClick={ ()=>lb24GetEvents('new') }
                                className="lb24-wp-select-liveblog-btn"
                            >
                                Select Liveblog
                            </button>
                            <a target="_blank" href={lb24BlockData.getLb24Config.LB24_URL}>
                                <span className="lb24-wp-create-new-btn">
                                    Create a New Liveblog
                                </span>
                            </a>
                        </div>
                    }
                    </div>
                    :
                    <div id="lb24-login" className="lb24-wp-login">
                        <img className="lb24-wp-login-logo" src={lb24BlockData.getLb24Config.LB24_LOGO_ICON}/>
                        <a 
                            href={lb24BlockData.getLb24Config.LB24_SETTINGS}
                            className="lb24-wp-login-btn"
                        >
                            Log in
                        </a>
                        <span className="lb24-wp-login-desc">No account yet? Go to
                            <a className="lb24-wp-login-portal" href={lb24BlockData.getLb24Config.LB24_URL} target="_blank"> 24liveblog </a>
                        and create new one</span>
                    </div>
                }
                <div id="lb24-wp-event-list-modal">
                    <div className="lb24-wp-event-lists">
                        <div className="lb24-wp-label">
                            <span className="lb24-wp-title">24liveblog Events</span>
                            <img className="lb24-wp-close" onClick={ lb24CloseModal } src={lb24BlockData.getLb24Config.LB24_CLOSE_ICON}/>
                        </div>
                        <div className="lb24-wp-container">
                            <div className="lb24-wp-header">
                                <img className="lb24-wp-logo" src={lb24BlockData.getLb24Config.LB24_LOGO_ICON}/>
                                <div className="lb24-wp-input">
                                    <input className="lb24-wp-input-text" onChange={(e) => {setAttributes({searchInputValue: e.target.value})}} value={searchInputValue}/>
                                    <img 
                                        onClick={ ()=>lb24GetEvents('search') }
                                        className="lb24-wp-search" 
                                        src={lb24BlockData.getLb24Config.LB24_SEARCH_ICON}
                                    />
                                </div>
                                <img onClick={ ()=>lb24GetEvents('reload') } className="lb24-wp-reload" src={lb24BlockData.getLb24Config.LB24_RELOAD_ICON}/>
                            </div>
                            {loading ? 
                                <div>
                                    <img id="lb24-wp-event-loading" src={lb24BlockData.getLb24Config.LB24_LOADING_BLACK_ICON}/>
                                </div>
                                :
                                eventList.map((event) => (
                                <div className="lb24-wp-event">
                                    <div className="lb24-wp-event-info">
                                        <img className="lb24-wp-event-cover" src={event.cover}/>
                                        <div className="lb24-wp-event-text">
                                            <p className="lb24-wp-event-title">{ event.title }</p>
                                            <span className="lb24-wp-event-created">Started : { moment(event.created * 1000).format('MMM DD, HH:mm') }</span>
                                        </div>
                                        <div className="lb24-wp-event-action">
                                            <select onChange={(value)=>lb24ChangeEmbedType(event.eid, value)} className="lb24-wp-event-embed">
                                                <option value="embed">Embed</option>
                                                <option value="iFrame">iFrame</option>
                                                <option value="AMP">AMP</option>
                                            </select>
                                            <button 
                                                className="lb24-wp-event-add" 
                                                onClick={ ()=>lb24SetCode(event.eid, event.cover, event.title) }
                                            >
                                            Add
                                            </button>
                                        </div>
                                    </div>
                                    {
                                        eventSettings[event.eid].embedType !== 'embed' &&
                                        <div className="lb24-event-size">
                                            <div className="lb24-event-size-height">
                                                <span className="lb24-event-size-height-label">Height</span>
                                                <input onChange={(value)=>lb24ChangeHeight(event.eid, value)} type="text" className="lb24-event-size-height-value" value={eventSettings[event.eid].height} />
                                            </div>
                                            <div className="lb24-event-size-width">
                                                <span className="lb24-event-size-width-label">Width</span>
                                                <input onChange={(value)=>lb24ChangeWidth(event.eid, value)} type="text" className="lb24-event-size-width-value" value={eventSettings[event.eid].width} />
                                            </div>
                                        </div>
                                    }
                                </div>
                                ))
                            }
                            <div className="lb24-wp-event-pagination">
                                <img 
                                    onClick={ ()=>lb24GetEvents('previous') } 
                                    className="lb24-wp-event-previous-page" 
                                    src={lb24BlockData.getLb24Config.LB24_PREVIOUS_PAGE_ICON}
                                />
                                <img 
                                    onClick={ ()=>lb24GetEvents('next') } 
                                    className="lb24-wp-event-next-page" 
                                    src={lb24BlockData.getLb24Config.LB24_NEXT_PAGE_ICON}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    },

    /**
     * The save function defines the way in which the different attributes should be combined
     * into the final markup, which is then serialized by Gutenberg into post_content.
     *
     * The "save" property must be specified and must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     *
     * @param {Object} props Props.
     * @returns {Mixed} JSX Frontend HTML.
     */
    save: ( props ) => {
        const { attributes: {eid, cover, title, eventSettings}} = props;
        const embedType = eventSettings[eid] && eventSettings[eid].embedType;
        
        if (embedType === 'embed') {
            return (
                <div>
                    <div id="LB24_LIVE_CONTENT" data-eid={eid}>
                    </div>
                    <script src="https://v.24liveblog.com/24.js">
                    </script>
                </div>
            );
        } else if (embedType === 'iFrame') {
            return (
                <div>
                    <iframe name="lb24" frameborder="0" height={eventSettings[eid].height} width={eventSettings[eid].width} class="lb24-iframe" scrolling="auto" src={`//v.24liveblog.com/iframe/?id=${eid}`}></iframe>
                    <script src="https://v.24liveblog.com/iframe.js"></script>
                </div>
            );
        } else if (embedType === 'AMP') {
            return (
                <div>
                    <amp-iframe width={eventSettings[eid].width} height={eventSettings[eid].height} sandbox="allow-scripts allow-same-origin allow-top-navigation" layout="responsive" frameborder="0" resizable src={`https://v.24liveblog.com/iframe/?id=${eid}`} style="position: relative">
                    <amp-img layout="fill" src="https://cdn.24liveblog.com/transparent.png" placeholder></amp-img><div overflow tabindex="0" role="button" aria-label="Read more" style="background: #0088cc; color: #fff; border-radius: 4px; position: absolute; top: 50%; left: 50%; transform: translate(-50%); font-size: 12px; font-weight: 300; padding: 5px 20px">Start View Liveblog</div>
                    </amp-iframe>
                </div>
            );
        }
    },
} );
