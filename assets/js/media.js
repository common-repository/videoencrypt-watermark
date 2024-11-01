/**
 * Adds a watermark Now button and displays stats in Media Attachment Details Screen
 */

jQuery(document).ready(function (){
 	(function ( $, _ ) {
		'use strict';

		// Local reference to the WordPress media namespace.
		const watermarkMedia  = wp.media,
			sharedTemplate2 = "<div class='watermark_media'><label class='name watermark'><%= label2 %></label><div class='value'><%= value2 %></div></div>",
			template2       = _.template( sharedTemplate2 );

		/**
		 * Create the template2.
		 *
		 * @param {string} watermarkHTML
		 * @returns {Object}
		 */
		const prepareTemplate = function ( watermarkHTML ) {
			/**
			 * @var {array}  watermark_vars.strings  Localization strings.
			 * @var {object} watermark_vars          Object from wp_localize_script()
			 */
			return template2( {
				label2: watermark_vars.strings['stats_label'],
				value2: watermarkHTML
			} );
		};

		if ( 'undefined' !== typeof watermarkMedia.view &&
			'undefined' !== typeof watermarkMedia.view.Attachment.Details.TwoColumn ) {
			// Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
			let watermarkMediaTwoColumn = watermarkMedia.view.Attachment.Details.TwoColumn;

			/**
			 * Add watermark details to attachment.
			 *
			 * A similar view to media.view.Attachment.Details
			 * for use in the Edit Attachment modal.
			 *
			 * @see wp-includes/js/media-grid.js
			 */
			watermarkMedia.view.Attachment.Details.TwoColumn = watermarkMediaTwoColumn.extend( {
				initialize: function () {
					this.listenTo( this.model, 'change:watermark', this.render );
				},

				render: function () {
					// Ensure that the main attachment fields are rendered.
					watermarkMedia.view.Attachment.prototype.render.apply( this, arguments );

					const watermarkHTML = this.model.get( 'watermark' );
					if ( typeof watermarkHTML === 'undefined' ) {
						return this;
					}

					this.model.fetch();
					/**
					 * Detach the views, append our custom fields, make sure that our data is fully updated
					 * and re-render the updated view.
					 */
					//this.views.detach();
					this.$el.find( '.settings' ).append( prepareTemplate( watermarkHTML ) );

					const encryptHTML = this.model.get( 'encrypt' );
					if ( typeof encryptHTML !== 'undefined' ) {
						this.$el.find( '.settings' ).append( prepareTemplate( encryptHTML ) );
					}
					

					this.views.render();
					return this;
				}
			} );
		}

		// Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
		let watermarkAttachmentDetails = watermarkMedia.view.Attachment.Details;

		/**
		 * Add watermark details to attachment.
		 */
		watermarkMedia.view.Attachment.Details = watermarkAttachmentDetails.extend( {
			initialize: function () {
				watermarkAttachmentDetails.prototype.initialize.apply( this, arguments );
				this.listenTo( this.model, 'change:watermark', this.render );
			},

			render: function () {
				// Ensure that the main attachment fields are rendered.
				watermarkMedia.view.Attachment.prototype.render.apply( this, arguments );

				const watermarkHTML = this.model.get( 'watermark' );
				if ( typeof watermarkHTML === 'undefined' ) {
					return this;
				}

				this.model.fetch();
				
				/**
				 * Detach the views, append our custom fields, make sure that our data is fully updated
				 * and re-render the updated view.
				 */
				//this.views.detach();
				this.$el.append( prepareTemplate( watermarkHTML ) );


				const encryptHTML = this.model.get( 'encrypt' );
				if ( typeof encryptHTML !== 'undefined' ) {
					this.$el.append( prepareTemplate( encryptHTML ) );
				}
				

				return this;
			}
		} );


	})( jQuery, _ );
});
