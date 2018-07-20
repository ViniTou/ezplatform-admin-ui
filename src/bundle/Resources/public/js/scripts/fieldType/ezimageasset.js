(function (global) {
    const SELECTOR_FIELD = '.ez-field-edit--ezimageasset';
    const SELECTOR_INPUT_FILE = 'input[type="file"]';
    const SELECTOR_LABEL_WRAPPER = '.ez-field-edit__label-wrapper';
    const SELECTOR_ALT_WRAPPER = '.ez-field-edit-preview__image-alt';
    const SELECTOR_INPUT_ALT = '.ez-field-edit-preview__image-alt .ez-data-source__input';
    const SELECTOR_INPUT_DESTINATION_CONTENT_ID = '.ez-field-edit__destination_content_id';

    const token = document.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = document.querySelector('meta[name="SiteAccess"]').content;

    class EzImageAssetPreviewField extends global.eZ.BasePreviewField {

        getImageData(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();

                reader.onload = event => resolve(event.target.result);
                reader.onerror = event => reject(event);
                reader.readAsDataURL(file);
            });
        }

        createImageDraftPayload(file, data, targetPath) {
            return {
                ContentCreate: {
                    ContentType: {
                        _href: "/api/ezp/v2/content/types/5",
                    },
                    mainLanguageCode: 'eng-GB',
                    LocationCreate: {
                        ParentLocation: {
                            _href: '/api/ezp/v2/content/locations' + targetPath || '/1/43/51'
                        },
                        sortField: 'PATH',
                        sortOrder: 'ASC'
                    },
                    Section: {
                        _href: '/api/ezp/v2/content/sections/3'
                    },
                    fields: {
                        field: [
                            {
                                fieldDefinitionIdentifier: 'name',
                                fieldValue: file.name
                            },
                            {
                                fieldDefinitionIdentifier: 'image',
                                fieldValue: {
                                    fileName: file.name,
                                    fileSize: file.size,
                                    data: data.substr(data.indexOf(",") + 1)
                                }
                            }
                        ]
                    }
                }
            };
        }

        createImage(file, targetPath) {
            this.getImageData(file)
                .then((data) => {
                    const request = new Request('/api/ezp/v2/content/objects', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/vnd.ez.api.Content+json',
                            'Content-Type': 'application/vnd.ez.api.ContentCreate+json',
                            'X-Siteaccess': siteaccess,
                            'X-CSRF-Token': token
                        },
                        body: JSON.stringify(this.createImageDraftPayload(file, data, targetPath)),
                        mode: 'same-origin',
                        credentials: 'same-origin'
                    });

                    return fetch(request).then((response) => {
                        return response.json();
                    });
                })
                .then((response) => {
                    const url = response.Content.CurrentVersion.Version._href;
                    const request = new Request(url, {
                        method: 'POST',
                        headers: {
                            'X-Http-Method-Override': 'PUBLISH',
                            'Accept': 'application/json',
                            'X-Siteaccess': siteaccess,
                            'X-CSRF-Token': token
                        },
                        mode: 'same-origin',
                        credentials: 'same-origin'
                    });

                    return new Promise((resolve, reject) => {
                        fetch(request).then(() => {
                            resolve(response);
                        });
                    });
                })
                .then((response) => {
                    document.body.dispatchEvent(new CustomEvent('ez-notify', {
                        detail: {
                            label: 'success',
                            message: 'Image has been published and can now be reused'
                        }
                    }));

                    const imageField = response.Content.CurrentVersion.Version.Fields.field.reduce((imageField, field) => {
                        if (field.fieldDefinitionIdentifier === 'image') {
                            return field;
                        }

                        return imageField;
                    });

                    const preview = this.fieldContainer.querySelector('.ez-field-edit__preview');
                    const image = preview.querySelector('.ez-field-edit-preview__media');
                    image.setAttribute('src', imageField.fieldValue.uri);

                    const nameContainer = preview.querySelector('.ez-field-edit-preview__file-name');
                    const sizeContainer = preview.querySelector('.ez-field-edit-preview__file-size');

                    nameContainer.innerHTML = imageField.fieldValue.fileName;
                    nameContainer.title = imageField.fieldValue.fileName;
                    sizeContainer.innerHTML = this.formatFileSize(imageField.fieldValue.fileSize);
                    sizeContainer.title = this.formatFileSize(imageField.fieldValue.fileSize);

                    preview.querySelector('.ez-field-edit-preview__action--preview').href = imageField.fieldValue.uri;

                    this.fieldContainer
                        .querySelector(SELECTOR_INPUT_DESTINATION_CONTENT_ID)
                        .setAttribute('value', response.Content._id);

                    this.showPreview();
                });
        }

        loadPreview(response) {
            if (response.Content.CurrentVersion) {
                response = response.Content;
            }

            const imageField = response.CurrentVersion.Version.Fields.field.reduce((imageField, field) => {
                if (field.fieldDefinitionIdentifier === 'image') {
                    return field;
                }

                return imageField;
            });

            const preview = this.fieldContainer.querySelector('.ez-field-edit__preview');
            const image = preview.querySelector('.ez-field-edit-preview__media');
            image.setAttribute('src', imageField.fieldValue.uri);
            const nameContainer = preview.querySelector('.ez-field-edit-preview__file-name');
            const sizeContainer = preview.querySelector('.ez-field-edit-preview__file-size');

            nameContainer.innerHTML = imageField.fieldValue.fileName;
            nameContainer.title = imageField.fieldValue.fileName;
            sizeContainer.innerHTML = this.formatFileSize(imageField.fieldValue.fileSize);
            sizeContainer.title = this.formatFileSize(imageField.fieldValue.fileSize);

            preview.querySelector('.ez-field-edit-preview__action--preview').href = imageField.fieldValue.uri;

            this.fieldContainer
                .querySelector(SELECTOR_INPUT_DESTINATION_CONTENT_ID)
                .setAttribute('value', response.ContentInfo.Content._id);
        }

        openUDW(event) {
            const udwContainer = document.getElementById('react-udw');
            const config = JSON.parse(event.currentTarget.dataset.udwConfig);

            const closeUDW = () => ReactDOM.unmountComponentAtNode(udwContainer);
            const onConfirm = (items) => {
                closeUDW();
                this.loadPreview(items[0]);
                this.showPreview();
            };
            const onCancel = () => closeUDW();
            const canSelectContent = ({item, itemsCount}, callback) => {
                const isAllowedContentType = item.ContentInfo.Content.ContentTypeInfo.identifier === 'image';
                callback(isAllowedContentType);
            };

            ReactDOM.render(React.createElement(eZ.modules.UniversalDiscovery, Object.assign({
                onConfirm,
                onCancel,
                canSelectContent,
                confirmLabel: 'View content',
                title: 'Browse content',
                multiple: false,
                startingLocationId: parseInt(event.currentTarget.dataset.startingLocationId, 10),
                restInfo: {token, siteaccess},
            }, config)), udwContainer);
        }

        /**
         * Checks if file size is an allowed limit
         *
         * @method handleInputChange
         * @param {Event} event
         */
        handleInputChange(event) {
            const file = event.currentTarget.files[0];
            const targetPath = this.fieldContainer.querySelector('.ez-data-source__btn-add').dataset.defaultLocationPath;

            if (this.maxFileSize > 0 && file.size > this.maxFileSize) {
                return this.resetInputField();
            }

            this.fieldContainer.querySelector('.ez-field-edit__option--remove-media').checked = false;

            this.createImage(file, targetPath);

        }

        /**
         * Initializes the preview
         *
         * @method init
         */
        init() {
            super.init();

            this.btnSelect = this.fieldContainer.querySelector('.ez-data-source__btn-select');
            this.btnSelect.addEventListener('click', (event) => {
                event.preventDefault();
                this.openUDW(event);
            }, false);
        }
    }

    class EzImageAssetFieldValidator extends global.eZ.BaseFileFieldValidator {
    }

    [...document.querySelectorAll(SELECTOR_FIELD)].forEach(fieldContainer => {
        const validator = new EzImageAssetFieldValidator({
            classInvalid: 'is-invalid',
            fieldContainer,
            eventsMap: [
                {
                    selector: `${SELECTOR_INPUT_FILE}`,
                    eventName: 'change',
                    callback: 'validateInput',
                    errorNodeSelectors: [SELECTOR_LABEL_WRAPPER],
                },
                {
                    selector: SELECTOR_INPUT_ALT,
                    eventName: 'blur',
                    callback: 'validateAltInput',
                    invalidStateSelectors: ['.ez-data-source__field--alternativeText'],
                    errorNodeSelectors: [`${SELECTOR_ALT_WRAPPER} .ez-data-source__label-wrapper`],
                },
                {
                    isValueValidator: false,
                    selector: `${SELECTOR_INPUT_FILE}`,
                    eventName: 'invalidFileSize',
                    callback: 'showFileSizeError',
                    errorNodeSelectors: [SELECTOR_LABEL_WRAPPER],
                },
                {
                    isValueValidator: false,
                    selector: SELECTOR_INPUT_ALT,
                    eventName: 'cancelErrors',
                    callback: 'cancelErrors',
                    invalidStateSelectors: ['.ez-data-source__field--alternativeText'],
                    errorNodeSelectors: [`${SELECTOR_ALT_WRAPPER} .ez-data-source__label-wrapper`],
                }
            ],
        });

        const previewField = new EzImageAssetPreviewField({
            validator,
            fieldContainer,
            fileTypeAccept: fieldContainer.querySelector(SELECTOR_INPUT_FILE).accept
        });

        previewField.init();

        global.eZ.fieldTypeValidators = global.eZ.fieldTypeValidators ?
            [...global.eZ.fieldTypeValidators, validator] :
            [validator];
    });
})(window);
