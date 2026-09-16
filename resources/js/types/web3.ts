export const polygonAmoyChainId = 80002;

export const campaignContractAbi = [
    {
        type: 'function',
        name: 'donate',
        stateMutability: 'payable',
        inputs: [{ name: 'campaignId', type: 'uint256' }],
        outputs: [],
    },
    {
        type: 'function',
        name: 'withdraw',
        stateMutability: 'nonpayable',
        inputs: [{ name: 'campaignId', type: 'uint256' }],
        outputs: [],
    },
] as const;

export const web3ContractAddress = import.meta.env.VITE_WEB3_CAMPAIGN_CONTRACT_ADDRESS as `0x${string}` | undefined;
