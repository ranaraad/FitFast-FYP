const ALIASED_MEASUREMENT_PAIRS = [
  ["waist_cm", "waist_circumference"],
  ["hips_cm", "hips_circumference"],
];

const aliasLookup = ALIASED_MEASUREMENT_PAIRS.reduce((acc, [primary, alias]) => {
  acc[primary] = alias;
  acc[alias] = primary;
  return acc;
}, {});

export const hydrateMeasurementAliases = (measurements = {}) => {
  const normalized = { ...measurements };

  ALIASED_MEASUREMENT_PAIRS.forEach(([primary, alias]) => {
    const hasPrimary = Object.prototype.hasOwnProperty.call(normalized, primary);
    const hasAlias = Object.prototype.hasOwnProperty.call(normalized, alias);

    if (hasPrimary) {
      normalized[alias] = normalized[primary];
      return;
    }

    if (hasAlias) {
      normalized[primary] = normalized[alias];
    }
  });

  return normalized;
};

export const mirrorMeasurementValue = (measurements, field, value) => {
  const next = { ...measurements, [field]: value };
  const aliasField = aliasLookup[field];

  if (aliasField && aliasField !== field) {
    next[aliasField] = value;
  }

  return next;
};
