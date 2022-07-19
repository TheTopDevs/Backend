require("dotenv").config();
const { ethers } = require("hardhat");

async function main() {

  const NftHt = await ethers.getContractFactory("NftHtERC1155");
  const nftHt = await NftHt.deploy("NftHtERC1155", "HTXCE");

  await nftHt.deployed();
  console.log("Success! Contract was deployed to: ", nftHt.address);

  await nftHt.mint(10, `https://ipfs.io/ipfs/${process.env.IPFS_HASH}`)

  console.log("NFT successfully minted");
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
