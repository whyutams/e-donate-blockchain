// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

contract SafeGiveCampaign {
    struct Campaign {
        address payable organizer;
        uint256 targetAmount;
        uint256 deadline;
        uint256 collectedAmount;
        bool withdrawn;
    }

    uint256 public nextCampaignId;
    mapping(uint256 => Campaign) public campaigns;

    event CampaignCreated(
        uint256 indexed campaignId,
        address indexed organizer,
        uint256 targetAmount,
        uint256 deadline
    );

    event DonationReceived(
        uint256 indexed campaignId,
        address indexed donor,
        uint256 amount
    );

    event WithdrawalCompleted(
        uint256 indexed campaignId,
        address indexed organizer,
        uint256 amount
    );

    function createCampaign(uint256 targetAmount, uint256 deadline)
        external
        returns (uint256 campaignId)
    {
        require(targetAmount > 0, "Target must be positive");
        require(deadline > block.timestamp, "Deadline must be in the future");

        campaignId = nextCampaignId++;
        campaigns[campaignId] = Campaign({
            organizer: payable(msg.sender),
            targetAmount: targetAmount,
            deadline: deadline,
            collectedAmount: 0,
            withdrawn: false
        });

        emit CampaignCreated(campaignId, msg.sender, targetAmount, deadline);
    }

    function donate(uint256 campaignId) external payable {
        Campaign storage campaign = campaigns[campaignId];

        require(campaign.organizer != address(0), "Campaign not found");
        require(!campaign.withdrawn, "Campaign already withdrawn");
        require(block.timestamp <= campaign.deadline, "Campaign has ended");
        require(msg.value > 0, "Donation must be positive");

        campaign.collectedAmount += msg.value;

        emit DonationReceived(campaignId, msg.sender, msg.value);
    }

    function withdraw(uint256 campaignId) external {
        Campaign storage campaign = campaigns[campaignId];

        require(campaign.organizer == msg.sender, "Not campaign organizer");
        require(!campaign.withdrawn, "Already withdrawn");
        require(
            campaign.collectedAmount >= campaign.targetAmount ||
                block.timestamp > campaign.deadline,
            "Target or deadline condition not met"
        );
        require(campaign.collectedAmount > 0, "No funds to withdraw");

        campaign.withdrawn = true;
        uint256 amount = campaign.collectedAmount;
        (bool sent, ) = campaign.organizer.call{value: amount}("");
        require(sent, "Withdrawal transfer failed");

        emit WithdrawalCompleted(campaignId, campaign.organizer, amount);
    }

    function getCampaign(uint256 campaignId)
        external
        view
        returns (Campaign memory)
    {
        return campaigns[campaignId];
    }
}
