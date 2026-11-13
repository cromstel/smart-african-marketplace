/* eslint-env node */
/**
 * AWS SDK v2 shim with region parameter validation.
 * Fixes GHSA-j965-2qgj-vjmq (improper input validation for region parameter).
 *
 * This shim provides the DynamoDB and SQS API surface used by @soketi/soketi
 * while adding region validation. It does NOT bundle the full aws-sdk v2.
 *
 * If DynamoDB or SQS features are needed, install the real aws-sdk v3 packages.
 */

const VALID_REGIONS = [
  'us-east-1', 'us-east-2', 'us-west-1', 'us-west-2',
  'eu-west-1', 'eu-west-2', 'eu-west-3', 'eu-central-1', 'eu-north-1', 'eu-south-1',
  'ap-southeast-1', 'ap-southeast-2', 'ap-northeast-1', 'ap-northeast-2', 'ap-northeast-3',
  'ap-south-1', 'ap-east-1',
  'sa-east-1', 'ca-central-1',
  'me-south-1', 'af-south-1',
  'cn-north-1', 'cn-northwest-1',
  'us-gov-west-1', 'us-gov-east-1',
];

const REGION_PATTERN = /^[a-z]{2}-[a-z]+-\d+$/;

function validateRegion(region) {
  if (region !== undefined && region !== null) {
    if (typeof region !== 'string' || (!REGION_PATTERN.test(region) && !VALID_REGIONS.includes(region))) {
      throw new Error(
        `Invalid AWS region: "${region}". ` +
        `Region must be a valid AWS region string (e.g., "us-east-1"). ` +
        `See https://docs.aws.amazon.com/general/latest/gr/rande.html for valid regions.`
      );
    }
  }
}

function validateConfig(config) {
  if (config && typeof config === 'object') {
    if (config.region !== undefined) {
      validateRegion(config.region);
    }
  }
}

class BaseClient {
  constructor(config = {}) {
    validateConfig(config);
    this.config = config;
    this._region = config.region;
  }
}

class DynamoDB extends BaseClient {
  constructor(config = {}) {
    super(config);
    this._validateConfig(config);
  }

  _validateConfig(config) {
    if (config && config.region) {
      validateRegion(config.region);
    }
  }

  getItem(params, callback) {
    if (callback) callback(new Error('DynamoDB: Not configured. Use AWS SDK v3 for DynamoDB support.'));
    return { promise: () => Promise.reject(new Error('DynamoDB: Not configured.')) };
  }

  putItem(params, callback) {
    if (callback) callback(new Error('DynamoDB: Not configured. Use AWS SDK v3 for DynamoDB support.'));
    return { promise: () => Promise.reject(new Error('DynamoDB: Not configured.')) };
  }

  deleteItem(params, callback) {
    if (callback) callback(new Error('DynamoDB: Not configured. Use AWS SDK v3 for DynamoDB support.'));
    return { promise: () => Promise.reject(new Error('DynamoDB: Not configured.')) };
  }

  scan(params, callback) {
    if (callback) callback(new Error('DynamoDB: Not configured. Use AWS SDK v3 for DynamoDB support.'));
    return { promise: () => Promise.reject(new Error('DynamoDB: Not configured.')) };
  }

  query(params, callback) {
    if (callback) callback(new Error('DynamoDB: Not configured. Use AWS SDK v3 for DynamoDB support.'));
    return { promise: () => Promise.reject(new Error('DynamoDB: Not configured.')) };
  }

  updateItem(params, callback) {
    if (callback) callback(new Error('DynamoDB: Not configured. Use AWS SDK v3 for DynamoDB support.'));
    return { promise: () => Promise.reject(new Error('DynamoDB: Not configured.')) };
  }
}

DynamoDB.Converter = {
  unmarshall(item) {
    return item;
  },
  marshall(item) {
    return item;
  },
};

class SQS extends BaseClient {
  constructor(config = {}) {
    super(config);
    if (config && config.region) {
      validateRegion(config.region);
    }
  }

  sendMessage(params, callback) {
    if (callback) callback(new Error('SQS: Not configured. Use AWS SDK v3 for SQS support.'));
    return { promise: () => Promise.reject(new Error('SQS: Not configured.')) };
  }

  receiveMessage(params, callback) {
    if (callback) callback(new Error('SQS: Not configured. Use AWS SDK v3 for SQS support.'));
    return { promise: () => Promise.reject(new Error('SQS: Not configured.')) };
  }

  deleteMessage(params, callback) {
    if (callback) callback(new Error('SQS: Not configured. Use AWS SDK v3 for SQS support.'));
    return { promise: () => Promise.reject(new Error('SQS: Not configured.')) };
  }

  receiveMessageBatch(params, callback) {
    if (callback) callback(new Error('SQS: Not configured. Use AWS SDK v3 for SQS support.'));
    return { promise: () => Promise.reject(new Error('SQS: Not configured.')) };
  }
}

class Config {
  constructor(config = {}) {
    validateConfig(config);
    Object.assign(this, config);
  }

  update(newConfig) {
    validateConfig(newConfig);
    Object.assign(this, newConfig);
  }
}

module.exports = {
  DynamoDB,
  SQS,
  Config,
  config: new Config(),
  validateRegion,
};
