(function(global, doc, Routing) {
    const versionA = doc.querySelector('#version_comparison_version_a_version');
    const versionB = doc.querySelector('#version_comparison_version_b_version');
    const comparisonButton = doc.querySelector('#version_comparison_compare');
    const sideBySideButton = doc.querySelector('#version_comparison_side_by_side');

    const loadVersion = () => {
        if (comparisonButton.getAttribute('disabled')) {
            redirectToComparison();
            return;
        }
        redirectToSideBySide();
    };

    const redirectToSideBySide = () => {
        const contentId = doc.querySelector('.ez-content-preview.comparison-preview').dataset.contentId;
        const datasetA = versionA.options[versionA.selectedIndex].dataset;
        const datasetB = versionB.options[versionB.selectedIndex].dataset;

        global.location.href = Routing.generate(
            'ezplatform.version.side_by_side_comparison',
            {
                contentInfoId: contentId,
                versionNoA: datasetA.versionNo,
                languageCodeA: datasetA.languageCode,
                versionNoB: datasetB.versionNo,
                languageCodeB: datasetB.languageCode,
            }
        );
    };

    const redirectToComparison = () => {
        const contentId = doc.querySelector('.ez-content-preview.comparison-preview').dataset.contentId;
        const datasetA = versionA.options[versionA.selectedIndex].dataset;
        const datasetB = versionB.options[versionB.selectedIndex].dataset;

        // Compare only in same language code, show some info or sth.
        if (datasetA.languageCode !== datasetB.languageCode) {
            return;
        }
        global.location.href = Routing.generate(
            'ezplatform.version.compare',
            {
                contentInfoId: contentId,
                versionNoA: datasetA.versionNo,
                versionNoB: datasetB.versionNo,
                languageCode: datasetA.languageCode,
            }
        );
    };

    versionA.addEventListener('change', loadVersion);
    versionB.addEventListener('change', loadVersion);
    comparisonButton.addEventListener('click', redirectToComparison);
    sideBySideButton.addEventListener('click', redirectToSideBySide);
})(window, window.document, window.Routing);
