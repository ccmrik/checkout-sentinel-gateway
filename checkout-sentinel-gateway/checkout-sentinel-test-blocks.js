const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { createElement } = wp.element;

const CheckoutSentinelTestLabel = ({ title }) => {
    return createElement('span', {}, title);
};

const CheckoutSentinelTestContent = ({ description }) => {
    return createElement('div', {}, [
        createElement('p', {}, description),
        createElement('label', {}, [
            'Number of simulated requests: ',
            createElement('input', {
                type: 'number',
                id: 'checkout_sentinel_test_requests',
                name: 'checkout_sentinel_test_requests',
                min: 1,
                max: 20,
                defaultValue: 5,
                style: { width: '60px', marginLeft: '10px' }
            })
        ])
    ]);
};

registerPaymentMethod({
    name: 'checkout_sentinel_test',
    label: createElement(CheckoutSentinelTestLabel, { title: '🛡️ Checkout Sentinel Test (Select Me!)' }),
    content: createElement(CheckoutSentinelTestContent, { description: 'This is a test gateway for Checkout Sentinel. Select this option to test the plugin functionality.' }),
    edit: createElement(CheckoutSentinelTestContent, { description: 'This is a test gateway for Checkout Sentinel. Select this option to test the plugin functionality.' }),
    canMakePayment: () => true,
    ariaLabel: '🛡️ Checkout Sentinel Test (Select Me!)',
    supports: {
        features: ['products'],
    },
});