import numpy as np


def mae(actual, predicted):
    a, p = np.asarray(actual, dtype=float), np.asarray(predicted, dtype=float)
    return float(np.mean(np.abs(a - p)))


def rmse(actual, predicted):
    a, p = np.asarray(actual, dtype=float), np.asarray(predicted, dtype=float)
    return float(np.sqrt(np.mean((a - p) ** 2)))


def wape(actual, predicted):
    a, p = np.asarray(actual, dtype=float), np.asarray(predicted, dtype=float)
    denominator = np.sum(np.abs(a))
    return float(np.sum(np.abs(a - p)) / denominator) if denominator else 0.0


def bias(actual, predicted):
    a, p = np.asarray(actual, dtype=float), np.asarray(predicted, dtype=float)
    return float(np.sum(p - a) / max(np.sum(np.abs(a)), 1.0))


def evaluate(actual, predicted):
    return {"mae": mae(actual, predicted), "rmse": rmse(actual, predicted), "wape": wape(actual, predicted), "bias": bias(actual, predicted)}
