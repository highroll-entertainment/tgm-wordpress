/**
 * Editor UI of the "TerraGaming Media Ad" block: the unit ID field and a placeholder (the ad itself
 * only renders on the published page). No build step: WordPress' globals.
 */
(function (blocks, element, blockEditor, components, i18n) {
  var el = element.createElement;
  var __ = i18n.__;
  var UNIT = /^TGM-[A-Z0-9]{3}-[A-Z0-9]{2,12}$/;

  blocks.registerBlockType("terragaming-ads/ad-unit", {
    edit: function (props) {
      var unit = props.attributes.unit || "";
      var valid = UNIT.test(unit);
      return el(
        "div",
        blockEditor.useBlockProps(),
        el(
          components.Placeholder,
          {
            icon: "megaphone",
            label: __("TerraGaming Media Ad", "terragaming-media-ads"),
            instructions: valid
              ? __("The ad appears here on the published page.", "terragaming-media-ads")
              : __(
                  "Enter the ad unit ID from the portal (Ad Units & Tags), e.g. TGM-ABC-HRS01.",
                  "terragaming-media-ads",
                ),
          },
          el(components.TextControl, {
            label: __("Ad unit ID", "terragaming-media-ads"),
            value: unit,
            __nextHasNoMarginBottom: true,
            onChange: function (value) {
              props.setAttributes({ unit: value.trim().toUpperCase() });
            },
          }),
        ),
      );
    },
    save: function () {
      return null;
    },
  });
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.i18n,
);
