import { opnameConfig } from '@/components/stock/configs';
import CountDocumentShow from '@/components/stock/count-document-show';
import type { StockCountDocument } from '@/lib/model';

type Props = {
    document: StockCountDocument;
};

export default ({ document }: Props) => (
    <CountDocumentShow config={opnameConfig} document={document} />
);
