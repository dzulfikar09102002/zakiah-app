import { opnameConfig } from '@/components/stock/configs';
import CountDocumentForm from '@/components/stock/count-document-form';
import type { StockCountDocument } from '@/lib/model';
import type { Option } from '@/types';

type Props = {
    document: StockCountDocument | null;
    locationOptions: Option[];
};

export default ({ document, locationOptions }: Props) => (
    <CountDocumentForm
        config={opnameConfig}
        document={document}
        locationOptions={locationOptions}
    />
);
