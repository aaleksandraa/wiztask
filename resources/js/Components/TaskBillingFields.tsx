import Input from '@/Components/ui/Input';
import Select from '@/Components/ui/Select';
import { minutesToHuman, optionsToSelect } from '@/lib/utils';

type Props = {
    billingType: string;
    onBillingTypeChange: (value: string) => void;
    billingTypeOptions: Record<string, string>;
    hourlyRate: string;
    onHourlyRateChange: (value: string) => void;
    fixedPrice: string;
    onFixedPriceChange: (value: string) => void;
    hours: string;
    onHoursChange: (value: string) => void;
    minutes: string;
    onMinutesChange: (value: string) => void;
    errors?: Partial<Record<string, string>>;
    showBillingType?: boolean;
    loggedMinutes?: number | null;
    isEditing?: boolean;
};

export default function TaskBillingFields({
    billingType,
    onBillingTypeChange,
    billingTypeOptions,
    hourlyRate,
    onHourlyRateChange,
    fixedPrice,
    onFixedPriceChange,
    hours,
    onHoursChange,
    minutes,
    onMinutesChange,
    errors,
    showBillingType = true,
    loggedMinutes,
    isEditing = false,
}: Props) {
    const isHourly = billingType === 'po_satu';
    const isFixed = billingType === 'fiksno';

    return (
        <>
            {showBillingType && (
                <Select
                    label="Tip naplate"
                    value={billingType}
                    onChange={(e) => onBillingTypeChange(e.target.value)}
                    options={optionsToSelect(billingTypeOptions)}
                />
            )}

            {isHourly && (
                <>
                    <Input
                        label="Satnica"
                        type="number"
                        step="0.01"
                        min="0"
                        value={hourlyRate}
                        onChange={(e) => onHourlyRateChange(e.target.value)}
                        error={errors?.hourly_rate}
                    />
                    <Input
                        label="Sati"
                        type="number"
                        min="0"
                        max="999"
                        value={hours}
                        onChange={(e) => onHoursChange(e.target.value)}
                        error={errors?.hours}
                        placeholder="0"
                    />
                    <Input
                        label="Minute"
                        type="number"
                        min="0"
                        max="59"
                        value={minutes}
                        onChange={(e) => onMinutesChange(e.target.value)}
                        error={errors?.minutes}
                        placeholder="0"
                    />
                    <p className="sm:col-span-2 text-xs text-neutral-500">
                        {isEditing && loggedMinutes != null && loggedMinutes > 0 ? (
                            <>Evidentirano: {minutesToHuman(loggedMinutes)}. Unesite dodatne sate ako treba.</>
                        ) : (
                            <>Broj sati možete unijeti sada ili naknadno na stranici taska kad posao bude završen.</>
                        )}
                    </p>
                </>
            )}

            {isFixed && (
                <Input
                    label="Fiksna cijena"
                    type="number"
                    step="0.01"
                    min="0"
                    value={fixedPrice}
                    onChange={(e) => onFixedPriceChange(e.target.value)}
                    error={errors?.fixed_price}
                />
            )}
        </>
    );
}
