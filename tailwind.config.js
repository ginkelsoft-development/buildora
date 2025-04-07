module.exports = {
    content: [
        './resources/**/*.{blade.php,js,vue}',  // Dit zorgt ervoor dat Tailwind de juiste bestanden scant
        './vendor/ginkelsoft/buildora/resources/**/*.{blade.php,js,vue}',  // Zorg ervoor dat je Buildora-componenten ook worden gescand
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
