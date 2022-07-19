require("dotenv").config();
const { ethers } = require("hardhat");

async function main() {

  const NftHt = await ethers.getContractFactory("NftHtCollection");
  const nftHt = await NftHt.deploy(
    "NftHtCollection",
    "HTXC",
    `https://ipfs.io/ipfs/${process.env.IPFH_HASH}/`
    );

  await nftHt.deployed();
  console.log("Success! Contract was deployed to: ", nftHt.address);

  await nftHt.mint(10) // 1
  await nftHt.mint(10) // 2

  console.log("NFT successfully minted");
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
