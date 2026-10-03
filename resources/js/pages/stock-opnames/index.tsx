import { opnameConfig } from '@/components/stock/configs';
import CountDocumentIndex from '@/components/stock/count-document-index';
import type { Pagination, StockCountDocument } from '@/lib/model';
import type { Option } from '@/types';

type Props = {
    pagination: Pagination<StockCountDocument>;
    locationOptions: Option[];
};

export default ({ pagination, locationOptions }: Props) => (
    <CountDocumentIndex
        config={opnameConfig}
        pagination={pagination}
        locationOptions={locationOptions}
    />
);
